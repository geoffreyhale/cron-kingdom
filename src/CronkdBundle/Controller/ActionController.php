<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use CronkdBundle\Form\ActionType;
use CronkdBundle\Model\Action;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

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

        $resourceManager = $this->get('cronkd.manager.resource');
        $resource = $resourceManager->get($resourceName);
        if (null === $resource) {
            throw new ResourceNotFoundException($resourceName);
        }
        if (!count($resource->getActions())) {
            throw $this->createNotFoundException('No actions to perform for ' . $resourceName);
        }

        $maxQuantityToProduce = $this->calculateMaxResourceAllocation($kingdom, $resource);

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
                $flashBag->add('success', $results['data']['outputQuantity'] . ' ' . $resourceName . ' successfully queued');
            }

            return $this->redirectToRoute('home');
        }

        return [
            'form'                => $form->createView(),
            'resource'            => $resourceName,
            'action'              => $resource->getActions()->first(),
            'maxQuantity'         => $maxQuantityToProduce,
            'resourceDescription' => $resource->getDescription(),
        ];
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float|int
     */
    private function calculateMaxResourceAllocation(Kingdom $kingdom, Resource $resource)
    {
        $maxQuantity = 10E99;

        $action = $resource->getActions()->first();
        if (null === $action) {
            return 0;
        }

        $inputs = $action->getInputs();
        $requiredInputs = [];
        /** @var ResourceActionInput $resourceActionInput */
        foreach ($inputs as $resourceActionInput) {
            $requiredInputs[$resourceActionInput->getResource()->getName()] = $resourceActionInput->getInputQuantity();
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
