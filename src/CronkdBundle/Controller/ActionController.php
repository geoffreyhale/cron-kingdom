<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Form\ActionType;
use CronkdBundle\Model\Action;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/action")
 */
class ActionController extends Controller
{
    /**
     * @Route("/{id}/produce", name="action_produce_materials")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Action:form.html.twig")
     */
    public function produceMaterialAction(Request $request, Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }

        $action = new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Action:produce', [
                'kingdomId' => $kingdom->getId(),
                'quantity'  => $action->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $flashBag = $this->get('session')->getFlashBag();
            if (isset($results['error'])) {
                $flashBag->add('danger' , $results['error']);
            } else {
                $flashBag->add('success', $action->getQuantity() . ' Material is queued up for production.');
            }

            return $this->redirectToRoute('homepage');
        }

        return [
            'actionName' => 'Produce Material',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/build", name="action_build_housing")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Action:form.html.twig")
     */
    public function buildHousingAction(Request $request, Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }

        $action = new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Action:build', [
                'kingdomId' => $kingdom->getId(),
                'quantity'  => $action->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $flashBag = $this->get('session')->getFlashBag();
            if (isset($results['error'])) {
                $flashBag->add('danger' , $results['error']);
            } else {
                $flashBag->add('success', $action->getQuantity() . ' Housing is queued up to be built.');
            }

            return $this->redirectToRoute('homepage');
        }

        return [
            'actionName' => 'Build Housing',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/train-military", name="action_train_military")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Action:form.html.twig")
     */
    public function trainMilitaryAction(Request $request, Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }

        $action = new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Action:trainMilitary', [
                'kingdomId' => $kingdom->getId(),
                'quantity'  => $action->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $flashBag = $this->get('session')->getFlashBag();
            if (isset($results['error'])) {
                $flashBag->add('danger' , $results['error']);
            } else {
                $flashBag->add('success', $action->getQuantity() . ' Military is queued up for training.');
            }

            return $this->redirectToRoute('homepage');
        }

        return [
            'actionName' => 'Train Military',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/train-hacker", name="action_train_hacker")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Action:form.html.twig")
     */
    public function trainHackerAction(Request $request, Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }

        $action = new Action();
        $form = $this->createForm(ActionType::class, $action, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Action:trainHacker', [
                'kingdomId' => $kingdom->getId(),
                'quantity'  => $action->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $flashBag = $this->get('session')->getFlashBag();
            if (isset($results['error'])) {
                $flashBag->add('danger' , $results['error']);
            } else {
                $flashBag->add('success', $action->getQuantity() . ' Hacker is queued up for training.');
            }

            return $this->redirectToRoute('homepage');
        }

        return [
            'actionName' => 'Train Hacker',
            'form' => $form->createView(),
        ];
    }
}
