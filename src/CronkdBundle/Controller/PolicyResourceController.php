<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Policy\Policy;
use CronkdBundle\Entity\Policy\PolicyResource;
use CronkdBundle\Form\Policy\PolicyResourceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/policy-resource")
 */
class PolicyResourceController extends CronkdController
{
    /**
     * @Route("/create/{id}", name="policy_resource_create")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function createAction(Request $request, Policy $policy)
    {
        $policyResource = new PolicyResource();
        $policyResource->setPolicy($policy);

        $form = $this->createForm(PolicyResourceType::class, $policyResource, [
            'world' => $policy->getWorld(),
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($policyResource);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Policy for ' . $policyResource->getResource()->getName() . ' Created!');

            return $this->redirectToRoute('policy_update', [
                'id' => $policy->getId(),
            ]);
        }

        return $this->render('CronkdBundle:PolicyResource:create.html.twig', [
            'world'  => $policy->getWorld(),
            'policy' => $policy,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="policy_resource_update")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function updateAction(Request $request, PolicyResource $policyResource)
    {
        $policy = $policyResource->getPolicy();

        $form = $this->createForm(PolicyResourceType::class, $policyResource, [
            'world' => $policyResource->getPolicy()->getWorld(),
        ]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($policy);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Policy Updated!');

            return $this->redirectToRoute('policy_update', [
                'id' => $policy->getId(),
            ]);
        }

        return $this->render('CronkdBundle:PolicyResource:update.html.twig', [
            'policyResource' => $policyResource,
            'policy'         => $policy,
            'world'          => $policy->getWorld(),
            'form'           => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="policy_resource_delete")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function deleteAction(Request $request, PolicyResource $policyResource)
    {
        $form = $this->createForm(PolicyResourceType::class, $policyResource);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($policy);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Policy Updated!');

            return $this->redirectToRoute('policy_update', [
                'policy' => $policy->getId(),
            ]);
        }

        return $this->render('CronkdBundle:Policy:update.html.twig', [
            'policy' => $policy,
            'world'  => $policy->getWorld(),
            'form'   => $form->createView(),
        ]);
    }
}
