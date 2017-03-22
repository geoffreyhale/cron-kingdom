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
 * @Route("/api/attack")
 */
class AttackController extends ApiController
{
    /**
     * @Route("", name="api_attack")
     * @Method("POST")
     */
    public function attackAction(Request $request)
    {
        $attackingService = $this->get('cronkd.service.attacking');
        $kingdomId = (int) $request->get('kingdomId');
        $targetKingdomId = (int) $request->get('targetKingdomId');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($targetKingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `targetKingdomId` (int)');
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

        $resourceMap = $this->buildResourcesMap($request);
        $army = $attackingService->buildArmy($kingdom, $resourceMap);
        if (!$army->containsResources()) {
            return $this->createErrorJsonResponse('No resources were sent to attack');
        }
        if (!$attackingService->kingdomHasResourcesToAttack($army)) {
            return $this->createErrorJsonResponse('Kingdom does not have enough resources');
        }

        $attackReport = $attackingService->attack($kingdom, $targetKingdom, $army);

        return $this->createSerializedJsonResponse([
            'data' => [
                'report' => $attackReport,
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function buildResourcesMap(Request $request)
    {
        $resources = [
            Resource::MILITARY => $request->get('military'),
        ];

        return $resources;
    }
}