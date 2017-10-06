<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use CronkdBundle\Form\Resource\ResourceActionInputType;
use CronkdBundle\Form\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource-action-input")
 */
class ResourceActionInputController extends Controller
{
    /**
     * @Route("/{id}", name="resource_action_input_index")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction(ResourceAction $resourceAction)
    {
        $inputs = $resourceAction->getInputs();

        return $this->render('CronkdBundle:ResourceActionInput:index.html.twig', [
            'inputs'         => $inputs,
            'resourceAction' => $resourceAction,
            'resource'       => $resourceAction->getResource(),
            'world'          => $resourceAction->getResource()->getWorld(),
        ]);
    }

    /**
     * @Route("/{id}/create", name="resource_action_input_create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, ResourceAction $resourceAction)
    {
        $resourceActionInput = new ResourceActionInput();
        $outputResource      = $resourceAction->getResource();
        $form = $this->createForm(ResourceActionInputType::class, $resourceActionInput, [
            'unavailable_resources' => $resourceAction->getUnavailableResourceInputIds(),
            'world'                 => $resourceAction->getResource()->getWorld(),
            'resource'              => $outputResource,
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $resourceActionInput->setResourceAction($resourceAction);

            $em = $this->getDoctrine()->getManager();
            $em->persist($resourceActionInput);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Input Created!');

            return $this->redirectToRoute('resource_action_input_index', [
                'id' => $resourceActionInput->getResourceAction()->getId(),
            ]);
        }

        return $this->render('CronkdBundle:ResourceActionInput:create.html.twig', [
            'form'           => $form->createView(),
            'input'          => $resourceActionInput,
            'resourceAction' => $resourceAction,
            'resource'       => $resourceAction->getResource(),
            'world'          => $resourceAction->getResource()->getWorld(),
        ]);
    }

    /**
     * @Route("/{id}/update", name="resource_action_input_update")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, ResourceActionInput $resourceActionInput)
    {
        $resourceAction  = $resourceActionInput->getResourceAction();
        $currentResource = $resourceActionInput->getResource();
        $outputResource  = $resourceAction->getResource();
        $form = $this->createForm(ResourceActionInputType::class, $resourceActionInput, [
            'resource'              => $outputResource,
            'unavailable_resources' => $resourceAction->getUnavailableResourceInputIds([$currentResource->getId()]),
            'world'                 => $resourceAction->getResource()->getWorld(),
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($resourceActionInput);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Input Updated!');

            return $this->redirectToRoute('resource_action_input_index', [
                'id' => $resourceActionInput->getResourceAction()->getId(),
            ]);
        }

        return $this->render('CronkdBundle:ResourceActionInput:update.html.twig', [
            'form'           => $form->createView(),
            'resourceAction' => $resourceAction,
            'input'          => $resourceActionInput,
            'resource'       => $resourceAction->getResource(),
            'world'          => $resourceAction->getResource()->getWorld(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="resource_action_input_delete")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(ResourceActionInput $input)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($input);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Input Removed!');

        return $this->redirectToRoute('resource_action_input_index', [
            'id' => $input->getResourceAction()->getId(),
        ]);
    }
}
