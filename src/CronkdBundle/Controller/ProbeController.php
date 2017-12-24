<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Event\Event;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Event\ProbeEvent;
use CronkdBundle\Form\ProbeAttemptType;
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

        $resourceManager = $this->get('cronkd.manager.resource');
        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $kingdomState = $kingdomManager->generateKingdomState($kingdom);

        $probeAttempt = new ProbeAttempt();
        $probeAttempt->setKingdom($kingdom);
        $form = $this->createForm(ProbeAttemptType::class, $probeAttempt, [
            'kingdomState' => $kingdomState,
            'resources'    => $resourceManager->getWorldResources($kingdom->getWorld()),
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

            if (isset($results['error'])) {
                throw new \Exception($results['error']);
            }

            return $this->redirect($this->generateUrl('event_probe_view', ['id' => $results['data']['event_id']]));
        }

        return [
            'form'         => $form->createView(),
            'kingdomState' => $kingdomState,
        ];
    }
}
