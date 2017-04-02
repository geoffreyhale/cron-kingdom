<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomPolicy;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Form\SelectPolicyType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/policy")
 */
class PolicyController extends CronkdController
{
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

        $em = $this->getDoctrine()->getManager();

        $kingdomPolicy = new KingdomPolicy();
        $form = $this->createForm(SelectPolicyType::class, $kingdomPolicy);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $policyManager = $this->get('cronkd.manager.policy');
            $policyManager->create($kingdomPolicy, $kingdom);

            return $this->redirectToRoute('homepage');
        }

        return [
            'form'     => $form->createView(),
            'policies' => $em->getRepository(Policy::class)->findBy([], ['name' => 'ASC']),
        ];
    }
}
