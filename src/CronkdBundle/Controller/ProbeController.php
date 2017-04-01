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

            $form = $this->createForm(ProbeRetryType::class, [
                'target'   => $probeAttempt->getTarget()->getId(),
                'quantity' => $probeAttempt->getQuantity(),
            ]);
            return $this->render('@Cronkd/Probe/results.html.twig', [
                'results'     => $results,
                'kingdom'     => $kingdom,
                'probeReport' => $probeAttempt,
                'form'        => $form->createView(),
            ]);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
