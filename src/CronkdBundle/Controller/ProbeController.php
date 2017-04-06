<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Form\AutoAttackPlanType;
use CronkdBundle\Form\ProbeAttemptType;
use CronkdBundle\Form\ProbeRetryType;
use CronkdBundle\Model\ProbeAttempt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/probe")
 */
class ProbeController extends CronkdController
{
    /**
     * @Route("/{id}/send", name="probe_send")
     * @Method({"GET", "POST"})
     * @ParamConverter(name="id", class="CronkdBundle:Kingdom")
     * @Template()
     */
    public function sendAction(Request $request, Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $probeAttempt = new ProbeAttempt();
        $form = $this->createForm(ProbeAttemptType::class, $probeAttempt, [
            'sourceKingdom' => $kingdom,
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Probe:send', [
                'kingdomId'       => $kingdom->getId(),
                'targetKingdomId' => $probeAttempt->getTarget()->getId(),
                'quantity'        => $probeAttempt->getQuantity(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $formAttack = null;
            $formAttackStrongDefense = null;
            $formRehack = null;
            $militaryToSend = 0;
            $defenderBonusMilitaryToSend = 0;
            if (isset($results['data']['report']['result']) && true == $results['data']['report']['result']) {
                $militaryToSend = $results['data']['report']['data'][Resource::MILITARY]['quantity'] + 1;
                $defenderBonusMilitaryToSend = ceil($results['data']['report']['data'][Resource::MILITARY]['quantity']*Policy::DEFENDER_BONUS) + 1;

                $formAttack = $this->createForm(AutoAttackPlanType::class, [
                    'target' => $probeAttempt->getTarget()->getId(),
                    'sourceKingdom' => $kingdom->getId(),
                    'militaryAllocations' => $militaryToSend,
                ]);
                $formAttackStrongDefense = $this->createForm(AutoAttackPlanType::class, [
                    'target' => $probeAttempt->getTarget()->getId(),
                    'sourceKingdom' => $kingdom->getId(),
                    'militaryAllocations' => $defenderBonusMilitaryToSend,
                ]);
            } else {
                $formRehack = $this->createForm(ProbeRetryType::class, [
                    'target'   => $probeAttempt->getTarget()->getId(),
                    'quantity' => $probeAttempt->getQuantity(),
                ]);
            }

            return $this->render('@Cronkd/Probe/results.html.twig', [
                'results'                     => $results,
                'kingdom'                     => $kingdom,
                'probeReport'                 => $probeAttempt,
                'formAttack'                  => ($formAttack === null ? null : $formAttack->createView()),
                'formAttackStrongDefense'     => ($formAttackStrongDefense === null ? null : $formAttackStrongDefense->createView()),
                'formRehack'                  => ($formRehack === null ? null : $formRehack->createView()),
                'defenderBonusMilitaryToSend' => $defenderBonusMilitaryToSend,
                'militaryToSend'              => $militaryToSend,
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
