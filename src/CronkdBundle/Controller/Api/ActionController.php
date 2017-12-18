<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Event\ActionEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Repository\LogRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/action")
 */
class ActionController extends ApiController
{
    /**
     * @Route("/", name="api_action")
     * @Method("GET")
     */
    public function performActionAction(Request $request)
    {
        $outputResource = $request->get('output');
        $kingdomId      = (int) $request->get('kingdomId');
        $quantity       = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }
        $resourceManager = $this->get('cronkd.manager.resource');
        try {
            $outputResourceObj = $resourceManager->get($outputResource);
        } catch (InvalidResourceException $e) {
            return $this->createErrorJsonResponse('Cannot perform action on `output`');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $action = $outputResourceObj->getActions()->first();
        $inputs = $action->getInputs();

        // Validate Kingdom has enough input resources to perform action
        /** @var ResourceActionInput $resourceActionInput */
        foreach ($inputs as $resourceActionInput) {
            $inputResource = $resourceActionInput->getResource();
            $kingdomAvailableResource = $kingdomManager->lookupResource($kingdom, $inputResource->getName());
            $requiredQuantity = $quantity * $resourceActionInput->getInputQuantity();
            if (!$kingdomAvailableResource || $requiredQuantity > $kingdomAvailableResource->getQuantity()) {
                return $this->createErrorJsonResponse('Not enough ' . $inputResource->getName() . ' to complete action');
            }
        }

        $kingdomState = $kingdomManager->generateKingdomState($kingdom);

        $inputQueues = [];
        $resourceManager = $this->get('cronkd.manager.resource');
        $queuePopulator = $this->get('cronkd.queue_populator');
        foreach ($inputs as $resourceActionInput) {
            $inputResource = $resourceActionInput->getResource();
            $kingdomResource = $kingdomManager->lookupResource($kingdom, $inputResource->getName());
            $inputQuantity = $quantity * $resourceActionInput->getInputQuantity();
            $kingdomResource->removeQuantity($inputQuantity);
            if ($resourceActionInput->getRequeue()) {
                $resource = $resourceManager->get($inputResource->getName());
                $queueSize = $resourceActionInput->getQueueSize() + $this->calculateQueueModifier($kingdomState->getActivePolicyName(), $resource);

                $inputQueues[] = $queuePopulator->build(
                    $kingdom,
                    $kingdomResource->getResource(),
                    $queueSize,
                    $inputQuantity
                );
            }
        }

        $kingdomOutputResource = $kingdomManager->lookupResource($kingdom, $action->getResource()->getName());
        $outputResource = $resourceManager->get($outputResourceObj->getName());
        $outputQuantity = $quantity * $action->getOutputQuantity();
        $queueSize = $action->getQueueSize() + $this->calculateQueueModifier($kingdomState->getActivePolicyName(), $outputResource);

        $policyManager = $this->get('cronkd.manager.policy');
        $queueSizeModifier = $policyManager->calculateQueueSizeModifier($kingdom, $outputResource);
        $queueSize += $queueSizeModifier;

        $outputMultiplier = $policyManager->calculateOutputMultiplier($kingdom, $outputResource);
        $outputQuantity = floor($outputQuantity * $outputMultiplier);

        $outputQueue = $queuePopulator->build(
            $kingdom,
            $kingdomOutputResource->getResource(),
            $queueSize,
            $outputQuantity
        );

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            $action->getVerb() . ' ' . $quantity . ' ' . $outputResourceObj->getName()
        );

        return new JsonResponse([
            'data' => [
                'inputs'         => $inputQueues,
                'output'         => $outputQueue,
                'outputQuantity' => $outputQuantity,
            ],
        ]);
    }

    /**
     * @param string $policyName
     * @param Resource $resource
     * @return int
     */
    private function calculateQueueModifier(string $policyName, Resource $resource)
    {
        return 0;

        $queueLengthModifier = 0;
        if ($policyName == Policy::ECONOMIST) {
            if ($resource->getType()->getName() == ResourceType::POPULATION &&
                ($resource->getAttack() > 0 || $resource->getDefense() > 0 || $resource->getProbePower() > 0)) {
                $queueLengthModifier = 2;
            } elseif (($resource->getType()->getName() == ResourceType::POPULATION && $resource->getAttack() == 0) ||
                ($resource->getType()->getName() == ResourceType::MATERIAL)
            ) {
                $queueLengthModifier = -2;
            }
        }

        return $queueLengthModifier;
    }
}
