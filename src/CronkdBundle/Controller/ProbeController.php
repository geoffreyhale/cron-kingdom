<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
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

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $kingdomState = $kingdomManager->generateKingdomState($kingdom);

        $probeAttempt = new ProbeAttempt();
        $probeAttempt->setKingdom($kingdom);
        $form = $this->createForm(ProbeAttemptType::class, $probeAttempt, [
            'kingdomState'  => $kingdomState,
            'settings'      => $this->getParameter('cronkd.settings')
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = $this->forward('CronkdBundle:Api/Probe:send', [
                'kingdomId'       => $probeAttempt->getKingdom()->getId(),
                'targetKingdomId' => $probeAttempt->getTarget()->getId(),
                'quantities'      => $probeAttempt->getQuantities(),
            ]);

            $results = $response->getContent();
            $results = json_decode($results, true);

            $formAttack = null;
            $formAttackStrongDefense = null;
            $rehackForm = null;
            $militaryToSend = 0;
            $defenderBonusMilitaryToSend = 0;
            if (isset($results['data']['report']['result']) && true == $results['data']['report']['result']) {
                /*
                $militaryToSend = $results['data']['report']['data']['Resources'][Resource::MILITARY]['quantity'] + 1;
                $defenderBonusMilitaryToSend = ceil($results['data']['report']['data']['Resources'][Resource::MILITARY]['quantity']*Policy::DEFENDER_BONUS) + 1;

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
                */
            } else {
                $rehackForm = $this->createForm(ProbeRetryType::class, [
                    'target' => $probeAttempt->getTarget()->getId(),
                ], [
                    'quantities' => $probeAttempt->getQuantities(),
                ]);
            }

            return $this->render('@Cronkd/Probe/results.html.twig', [
                'results'                     => $results,
                'kingdom'                     => $kingdom,
                'kingdomState'                => $kingdomState,
                'probeReport'                 => $probeAttempt,
                //'formAttack'                  => ($formAttack === null ? null : $formAttack->createView()),
                //'formAttackStrongDefense'     => ($formAttackStrongDefense === null ? null : $formAttackStrongDefense->createView()),
                'rehackForm'                  => ($rehackForm === null ? null : $rehackForm->createView()),
                'defenderBonusMilitaryToSend' => $defenderBonusMilitaryToSend,
                'militaryToSend'              => $militaryToSend,
            ]);
        }

        return [
            'form'         => $form->createView(),
            'kingdomState' => $kingdomState,
        ];
    }
}
