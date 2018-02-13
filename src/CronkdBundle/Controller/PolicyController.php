<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Tech\Policy;
use CronkdBundle\Entity\Tech\PolicyInstance;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\Policy\PolicyType;
use CronkdBundle\Form\PolicyInstanceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/policy")
 */
class PolicyController extends CronkdController
{
    /**
     * @Route("/create/{id}", name="policy_create")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function createAction(Request $request, World $world)
    {
        $policy = new Policy();
        $policy->setWorld($world);

        $form = $this->createForm(PolicyType::class, $policy);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($policy);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Policy Created!');

            return $this->redirectToRoute('world_configure', [
                'world' => $policy->getWorld()->getId(),
                'tab'   => 'policies',
            ]);
        }

        return $this->render('CronkdBundle:Policy:create.html.twig', [
            'world'  => $policy->getWorld(),
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="policy_update")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template()
     */
    public function updateAction(Request $request, Policy $policy)
    {
        $form = $this->createForm(PolicyType::class, $policy);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($policy);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Policy Updated!');

            return $this->redirectToRoute('world_configure', [
                'world' => $policy->getWorld()->getId(),
                'tab'   => 'policies',
            ]);
        }

        return $this->render('CronkdBundle:Policy:update.html.twig', [
            'policy' => $policy,
            'world'  => $policy->getWorld(),
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/select/{id}", name="policy_select")
     * @ParamConverter("id", class="CronkdBundle:Kingdom")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function selectAction(Request $request, Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $policyManager = $this->get('cronkd.manager.policy');
        if (null !== $kingdom->getActivePolicy()) {
            $this->get('session')->getFlashBag()->add('warning', 'Kingdom already has an active policy!');

            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();

        $policyInstance = new PolicyInstance();
        $form = $this->createForm(PolicyInstanceType::class, $policyInstance);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $policyManager->create($policyInstance, $kingdom);

            $this->get('session')->getFlashBag()->add('success', 'Policy is now active!');

            return $this->redirectToRoute('homepage');
        }

        return [
            'form'     => $form->createView(),
            'policies' => $em->getRepository(Policy::class)->findBy([], ['name' => 'ASC']),
        ];
    }
}
