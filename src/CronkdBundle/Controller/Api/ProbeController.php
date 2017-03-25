<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Service\ProbingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $kingdomId = (int) $request->get('kingdomId');
        $targetKingdomId = (int) $request->get('targetKingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($targetKingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `targetKingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
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
        $resourceManager = $this->get('cronkd.manager.resource');

        $hackerResource = $resourceManager->get(Resource::HACKER);
        $availableHackers = $kingdomManager->lookupResource($kingdom, Resource::HACKER);
        if (!$availableHackers || $quantity > $availableHackers->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough hackers to complete action!');
        }

        /** @var ProbingService $probingService */
        $probingService = $this->get('cronkd.service.probing');
        $report = $probingService->probe($kingdom, $targetKingdom, $quantity);

        $queuePopulator = $this->get('cronkd.queue_populator');
        $hackerQueues = $queuePopulator->build($kingdom, $hackerResource, 1, $quantity);

        $availableHackers->removeQuantity($quantity);
        $em->persist($availableHackers);
        $em->flush();

        return $this->createSerializedJsonResponse([
            'data' => [
                'report'        => $report,
                'hacker_queues' => $hackerQueues,
            ],
        ]);
    }
}
