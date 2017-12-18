<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Form\Resource\ResourceActionType;
use CronkdBundle\Form\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource-action")
 */
class ResourceActionController extends Controller
{
    /**
     * @Route("/create/{id}", name="resource_action_create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, Resource $resource)
    {
        $resourceAction = new ResourceAction();
        $form = $this->createForm(ResourceActionType::class, $resourceAction, [
            'resource' => $resource,
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $resourceAction->setResource($resource);

            $em = $this->getDoctrine()->getManager();
            $em->persist($resourceAction);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Resource Action Updated!');

            return $this->redirectToRoute('resource_action_input_index', [
                'id' => $resourceAction->getId(),
            ]);
        }

        return $this->render('CronkdBundle:ResourceAction:create.html.twig', [
            'resourceAction' => $resourceAction,
            'resource'       => $resource,
            'world'          => $resource->getWorld(),
            'form'           => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="resource_action_update")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, ResourceAction $resourceAction)
    {
        $form = $this->createForm(ResourceActionType::class, $resourceAction, [
            'resource' => $resourceAction->getResource(),
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($resourceAction);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Resource Action Updated!');

            return $this->redirectToRoute('world_configure', [
                'world' => $resourceAction->getResource()->getWorld()->getId(),
                'tab'   => 'resources',
            ]);
        }

        return $this->render('CronkdBundle:ResourceAction:update.html.twig', [
            'resourceAction' => $resourceAction,
            'resource'       => $resourceAction->getResource(),
            'world'          => $resourceAction->getResource()->getWorld(),
            'form'           => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="resource_action_delete")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(ResourceAction $resourceAction)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($resourceAction);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Resource Action Removed!');

        return $this->redirectToRoute('resource_update', [
            'id' => $resourceAction->getResource()->getId(),
        ]);
    }
}
