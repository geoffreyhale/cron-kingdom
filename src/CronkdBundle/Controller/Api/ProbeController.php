<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;
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
     * @Route("/send", name="probe_send")
     * @Method("PUT")
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

        $hackerResource = $em->getRepository(Resource::class)->findOneBy(['name' => Resource::HACKER]);
        $availableHackers = $em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $hackerResource,
        ]);

        if (!$availableHackers || $quantity > $availableHackers->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough hackers to complete action!');
        }

        $probingService = $this->get('cronkd.service.probing');
        $report = $probingService->probe($targetKingdom, $quantity);
        $timeToReturn = 8;
        if (false === $report->getResult()) {
            $timeToReturn = 24;
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $hackerQueues = $queuePopulator->build($kingdom, $hackerResource, $timeToReturn, $quantity);

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
