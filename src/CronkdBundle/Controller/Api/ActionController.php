<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource;
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

        // @TODO: reimplement policies
        //$policyManager = $this->get('cronkd.manager.policy');
        //$queueLength = 8;
        //if ($policyManager->kingdomHasActivePolicy($kingdom, Policy::ECONOMIST)) {
        //    $queueLength -= 2;
        //}

        $inputQueues = [];
        $queuePopulator = $this->get('cronkd.queue_populator');
        foreach ($actionDefinition['inputs'] as $resourceName => $inputDefinition) {
            $kingdomResource = $kingdomManager->lookupResource($kingdom, $resourceName);
            $inputQuantity = $quantity * $inputDefinition['quantity'];
            $kingdomResource->removeQuantity($inputQuantity);
            if ($inputDefinition['requeue']) {
                $inputQueues[] = $queuePopulator->build(
                    $kingdom,
                    $kingdomResource->getResource(),
                    $inputDefinition['queue_size'],
                    $inputQuantity
                );
            }
        }

        $outputDefinition = $actionDefinition['output'];
        $kingdomOutputResource = $kingdomManager->lookupResource($kingdom, $outputResourceObj->getName());
        $outputQuantity = $quantity * $outputDefinition['quantity'];
        $outputQueue = $queuePopulator->build(
            $kingdom,
            $kingdomOutputResource->getResource(),
            $outputDefinition['queue_size'],
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
}
