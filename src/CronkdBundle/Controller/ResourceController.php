<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\ResourceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource")
 */
class ResourceController extends Controller
{
    /**
     * @Route("/create/{id}", name="resource_create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, World $world)
    {
        $resource = new Resource();
        $resource->setWorld($world);

        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($resource);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Resource Created!');

            return $this->redirectToRoute('world_configure', [
                'world' => $resource->getWorld()->getId(),
                'tab'   => 'resources',
            ]);
        }

        return $this->render('CronkdBundle:Resource:create.html.twig', [
            'resource' => $resource,
            'world'    => $resource->getWorld(),
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", requirements={"id" = "\d+"}, name="resource_update")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, Resource $resource)
    {
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($resource);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Resource Updated!');

            return $this->redirectToRoute('world_configure', [
                'world' => $resource->getWorld()->getId(),
                'tab'   => 'resources',
            ]);
        }

        return $this->render('CronkdBundle:Resource:update.html.twig', [
            'resource' => $resource,
            'world'    => $resource->getWorld(),
            'form'     => $form->createView(),
        ]);
    }
}
