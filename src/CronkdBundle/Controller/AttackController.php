<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\AttackLog;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Form\AttackPlanType;
use CronkdBundle\Model\AttackPlan;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/attack")
 */
class AttackController extends CronkdController
{
    /**
     * @Route("/{id}", name="attack")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Attack:send.html.twig")
     */
    public function attackAction(Request $request, Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $em = $this->getDoctrine()->getManager();
        $previousAttack = $em->getRepository(AttackLog::class)->findOneBy([
            'attacker' => $kingdom,
            'tick'     => $kingdom->getWorld()->getTick(),
        ]);
        if (null !== $previousAttack) {
            throw $this->createAccessDeniedException("You may only attack once per tick");
        }

        $attackPlan = new AttackPlan();
        $form = $this->createForm(AttackPlanType::class, $attackPlan, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Attack:attack', [
                'kingdomId'       => $kingdom->getId(),
                'targetKingdomId' => $attackPlan->getTarget()->getId(),
                'military'        => $attackPlan->getMilitaryAllocations(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            return $this->render('@Cronkd/Attack/results.html.twig', [
                'results' => $results,
                'kingdom' => $kingdom,
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
