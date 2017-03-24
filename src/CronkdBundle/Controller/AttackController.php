<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Form\AttackPlanType;
use CronkdBundle\Form\ProbeAttemptType;
use CronkdBundle\Model\AttackPlan;
use CronkdBundle\Model\ProbeAttempt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/attack")
 */
class AttackController extends Controller
{
    /**
     * @Route("/{id}", name="attack")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template("CronkdBundle:Attack:send.html.twig")
     */
    public function attackAction(Request $request, Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
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
