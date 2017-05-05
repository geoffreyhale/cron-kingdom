<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\ResourceType;
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
        $settings = $this->getParameter('cronkd.settings');
        $outputResourceSettings = $settings['resources'][$outputResourceObj->getName()];
        $actionDefinition = $outputResourceSettings['action'];

        // Validate Kingdom has enough input resources to perform action
        foreach ($actionDefinition['inputs'] as $resourceName => $inputDefinition) {
            $kingdomAvailableResource = $kingdomManager->lookupResource($kingdom, $resourceName);
            $requiredQuantity = $quantity * $inputDefinition['quantity'];
            if (!$kingdomAvailableResource || $requiredQuantity > $kingdomAvailableResource->getQuantity()) {
                return $this->createErrorJsonResponse('Not enough ' . $resourceName . ' to complete action');
            }
        }

        $kingdomState = $kingdomManager->generateKingdomState($kingdom);

        $inputQueues = [];
        $resourceManager = $this->get('cronkd.manager.resource');
        $queuePopulator = $this->get('cronkd.queue_populator');
        foreach ($actionDefinition['inputs'] as $resourceName => $inputDefinition) {
            if ($inputDefinition['requeue']) {
                $resource = $resourceManager->get($resourceName);
                $kingdomResource = $kingdomManager->lookupResource($kingdom, $resourceName);
                $inputQuantity = $quantity * $inputDefinition['quantity'];
                $kingdomResource->removeQuantity($inputQuantity);
                $queueSize = $inputDefinition['queue_size'] + $this->calculateQueueModifier($kingdomState->getActivePolicyName(), $resource);

                $inputQueues[] = $queuePopulator->build(
                    $kingdom,
                    $kingdomResource->getResource(),
                    $queueSize,
                    $inputQuantity
                );
            }
        }

        $outputDefinition = $actionDefinition['output'];
        $kingdomOutputResource = $kingdomManager->lookupResource($kingdom, $outputResourceObj->getName());
        $outputResource = $resourceManager->get($outputResourceObj->getName());
        $outputQuantity = $quantity * $outputDefinition['quantity'];
        $queueSize = $outputDefinition['queue_size'] + $this->calculateQueueModifier($kingdomState->getActivePolicyName(), $outputResource);

        $outputQueue = $queuePopulator->build(
            $kingdom,
            $kingdomOutputResource->getResource(),
            $queueSize,
            $outputQuantity
        );

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            $actionDefinition['verb'] . ' ' . $quantity . ' ' . $outputResourceObj->getName()
        );

        return new JsonResponse([
            'data' => [
                'inputs' => $inputQueues,
                'output' => $outputQueue,
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
        $queueLengthModifier = 0;
        if ($policyName == Policy::ECONOMIST) {
            if ($resource->getType()->getName() == ResourceType::POPULATION && $resource->getAttack() > 0) {
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
