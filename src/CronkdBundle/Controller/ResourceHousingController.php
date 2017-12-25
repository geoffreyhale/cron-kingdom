<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Entity\Resource\ResourceHousing;
use CronkdBundle\Form\Resource\ResourceActionType;
use CronkdBundle\Form\Resource\ResourceHousingType;
use CronkdBundle\Form\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resource-housing")
 */
class ResourceHousingController extends Controller
{
    /**
     * @Route("/add-all/{id}", name="resource_housing_add_all")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAllPopulationResourcesAction(Resource $resource)
    {
        $em = $this->getDoctrine()->getManager();
        $resourceManager = $this->get('cronkd.manager.resource');
        $resources = $resourceManager->getPopulationResources();
        foreach ($resources as $referencedResource) {
            $resourceHousing = new ResourceHousing();
            $resourceHousing->setOwningResource($resource);
            $resourceHousing->setReferencedResource($referencedResource);
            $em->persist($resourceHousing);
        }

        $em->flush();

        $this->get('session')->getFlashBag()->add('success', $resource->getName() . ' can now hold capacity for all population!');

        return $this->redirectToRoute('resource_update', [
            'id' => $resourceHousing->getOwningResource()->getId(),
        ]);
    }

    /**
     * @Route("/create/{id}", name="resource_housing_create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request, Resource $resource)
    {
        $resourceHousing = new ResourceHousing();
        $form = $this->createForm(ResourceHousingType::class, $resourceHousing, [
            'unavailable_resources' => $resource->getUnavailableResourceHousingIds(),
            'world'                 => $resource->getWorld(),
            'resource'              => $resource,
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $resourceHousing->setOwningResource($resource);

            $em = $this->getDoctrine()->getManager();
            $em->persist($resourceHousing);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $resource->getName() . ' can now hold capacity for resource');

            return $this->redirectToRoute('resource_update', [
                'id' => $resourceHousing->getOwningResource()->getId(),
            ]);
        }

        return $this->render('CronkdBundle:ResourceHousing:create.html.twig', [
            'resourceHousing' => $resourceHousing,
            'resource'        => $resource,
            'world'           => $resource->getWorld(),
            'form'            => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="resource_housing_delete")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(ResourceHousing $resourceHousing)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($resourceHousing);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Resource Housing Removed!');

        return $this->redirectToRoute('resource_update', [
            'id' => $resourceHousing->getOwningResource()->getId(),
        ]);
    }
}
