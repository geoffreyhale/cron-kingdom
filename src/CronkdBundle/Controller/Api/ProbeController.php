<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Service\ProbingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/probe")
 */
class ProbeController extends ApiController
{
    /**
     * @Route("/send", name="api_probe_send")
     * @Method("POST")
     */
    public function sendAction(Request $request)
    {
        $kingdomId       = (int) $request->get('kingdomId');
        $targetKingdomId = (int) $request->get('targetKingdomId');
        $quantities      = $request->get('quantities');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($targetKingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `targetKingdomId` (int)');
        }
        if (empty($quantities)) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantities` (array)');
        }
        if ($kingdomId == $targetKingdomId) {
            return $this->createErrorJsonResponse('`kingdomId` and `targetKingdomId` cannot be the same');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }
        $targetKingdom = $em->getRepository(Kingdom::class)->find($targetKingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Target Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $policyManager  = $this->get('cronkd.manager.policy');

        $probePower = 0;
        foreach ($quantities as $resourceName => $quantity) {
            $resource = $em->getRepository(Resource::class)->findOneByName($resourceName);
            if (null === $resource) {
                return $this->createErrorJsonResponse('Invalid resource "' . $resourceName . '"');
            }
            if (empty($quantity) || 0 >= $quantity) {
                return $this->createErrorJsonResponse('Invalid value for "' . $resourceName . '" (positive int)');
            }

            $kingdomResource = $kingdomManager->lookupResource($kingdom, $resourceName);
            if ($kingdomResource->getQuantity() < $quantity) {
                return $this->createErrorJsonResponse('Not enough "' . $resourceName . '"');
            }

            $probePowerMultiplier = $policyManager->calculateProbePowerMultiplier($kingdom, $resource);
            $resourceProbePower = ($quantity * $kingdomResource->getResource()->getProbePower());
            $probePower += $probePowerMultiplier * $resourceProbePower;
        }

        /** @var ProbingService $probingService */
        $probingService = $this->get('cronkd.service.probing');
        $report = $probingService->probe($kingdom, $targetKingdom, $probePower);

        $resourceManager = $this->get('cronkd.manager.resource');
        $queuePopulator = $this->get('cronkd.queue_populator');
        foreach ($quantities as $resourceName => $quantity) {
            $resource = $resourceManager->get($resourceName);
            $kingdomResource = $kingdomManager->lookupResource($kingdom, $resourceName);

            // Only requeue probes if success
            if ($report->getResult()) {
                $probeQueues = $queuePopulator->build($kingdom, $resource, 8, $quantity);
            }
            $kingdomResource->removeQuantity($quantity);
            $em->persist($kingdomResource);
        }
        $em->flush();

        return $this->createSerializedJsonResponse([
            'data' => [
                'event_id'      => $report->getProbeEvent()->getId(),
                'report'        => $report,
                'hacker_queues' => $probeQueues,
            ],
        ]);
    }
}
