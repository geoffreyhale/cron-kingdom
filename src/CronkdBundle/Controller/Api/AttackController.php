<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\AttackLog;
use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Service\AttackingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        /** @var AttackingService $attackingService */
        $attackingService = $this->get('cronkd.service.attacking');
        $kingdomId = (int) $request->get('kingdomId');
        $targetKingdomId = (int) $request->get('targetKingdomId');
        $quantities = $request->get('quantities');

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
        if (!$targetKingdom) {
            return $this->createErrorJsonResponse('Invalid Target Kingdom');
        }

        $previousAttack = $attackingService->numAttacksThisTick($kingdom);
        if (0 < $previousAttack) {
            return $this->createErrorJsonResponse('Kingdom has already attacked this tick');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        foreach ($quantities as $resourceName => $quantity) {
            $resource = $em->getRepository(Resource::class)->findOneByName($resourceName);
            if (null === $resource) {
                return $this->createErrorJsonResponse('Invalid resource "' . $resourceName . '"');
            }
            if (0 > $quantity) {
                return $this->createErrorJsonResponse('Invalid value for "' . $resourceName . '" (must be positive int)');
            }
            if (0 === $quantity) {
                unset($quantities[$resourceName]);
                continue;
            }

            $kingdomResource = $kingdomManager->lookupResource($kingdom, $resourceName);
            if ($kingdomResource->getQuantity() < $quantity) {
                return $this->createErrorJsonResponse('Not enough "' . $resourceName . '"');
            }
        }

        $attackReport = $attackingService->attack($kingdom, $targetKingdom, $quantities);

        return $this->createSerializedJsonResponse([
            'data' => [
                'event_id' => $attackReport->getAttackResultEvent()->getId(),
                'report'   => $attackReport,
            ],
        ]);
    }
}
