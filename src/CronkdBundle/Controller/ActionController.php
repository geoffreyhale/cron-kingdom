<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Form\ActionType;
use CronkdBundle\Model\Action;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/action")
 */
class ActionController extends CronkdController
{
    /**
     * @Route("/{resourceName}", name="action_perform")
     * @Method({"GET", "POST"})
     * @Template("CronkdBundle:Action:form.html.twig")
     */
    public function performActionAction(Request $request, $resourceName)
    {
        $kingdom = $this->extractKingdomFromCurrentUser();
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $settings = $this->getParameter('cronkd.settings');
        if (!isset($settings['resources'][$resourceName])) {
            throw $this->createNotFoundException('No actions to perform for ' . $resourceName);
        }

        $maxQuantityToProduce = $this->calculateMaxResourceAllocation($kingdom, $settings, $resourceName);

        $action = new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Action:performAction', [
                'output'    => $resourceName,
                'kingdomId' => $kingdom->getId(),
                'quantity'  => $action->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $flashBag = $this->get('session')->getFlashBag();
            if (null === $results) {
                $flashBag->add('danger', 'Unknown error occurred');
            } elseif (isset($results['error'])) {
                $flashBag->add('danger' , $results['error']);
            } else {
                $flashBag->add('success', $action->getQuantity() . ' ' . $resourceName . ' successfully queued');
            }

            return $this->redirectToRoute('homepage');
        }

        return [
            'form'                => $form->createView(),
            'resource'            => $resourceName,
            'maxQuantity'         => $maxQuantityToProduce,
            'actionDescription'   => $settings['resources'][$resourceName]['action']['description'],
            'resourceDescription' => $settings['resources'][$resourceName]['description'],
            'verb'                => $settings['resources'][$resourceName]['action']['verb'],
        ];
    }

    /**
     * @param Kingdom $kingdom
     * @param array $settings
     * @param string $resourceName
     * @return int
     */
    private function calculateMaxResourceAllocation(Kingdom $kingdom, array $settings, string $resourceName)
    {
        $maxQuantity = 10E99;

        $inputs = $settings['resources'][$resourceName]['action']['inputs'];
        $requiredInputs = [];
        foreach ($inputs as $inputResourceName => $inputResourceData) {
            $requiredInputs[$inputResourceName] = $inputResourceData['quantity'];
        }

        $em = $this->getDoctrine()->getManager();
        $kingdomResources = $em->getRepository(KingdomResource::class)->findSpecificResources(
            $kingdom,
            array_keys($requiredInputs)
        );

        if (!count($kingdomResources)) {
            $maxQuantity = 0;
        }

        foreach ($kingdomResources as $kingdomResource) {
            $quantity = floor($kingdomResource->getQuantity() / $requiredInputs[$kingdomResource->getResource()->getName()]);
            if ($quantity < $maxQuantity) {
                $maxQuantity = $quantity;
            }
        }

        return $maxQuantity;
    }
}
