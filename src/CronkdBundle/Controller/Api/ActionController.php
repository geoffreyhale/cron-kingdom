<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Event\ActionEvent;
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
     * @Route("/produce", name="api_action_product")
     * @Method("PUT")
     */
    public function produceAction(Request $request)
    {
        if ($this->getUser()->getVacation()) {
            return $this->createErrorJsonResponse('You are on vacation!');
        }

        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $resourceManager = $this->get('cronkd.manager.resource');

        $materialResource = $resourceManager->get(Resource::MATERIAL);
        $civilianResource = $resourceManager->get(Resource::CIVILIAN);
        $availableCivilians = $kingdomManager->lookupResource($kingdom, Resource::CIVILIAN);
        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action!');
        }

        $policyManager = $this->get('cronkd.manager.policy');
        $queueLength = 8;
        if ($policyManager->kingdomHasActivePolicy($kingdom, Policy::ECONOMIST)) {
            $queueLength -= 2;
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $civilianQueues = $queuePopulator->build($kingdom, $civilianResource, $queueLength, $quantity);
        $materialQueues = $queuePopulator->build($kingdom, $materialResource, $queueLength, $quantity);

        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            'Producing ' . $quantity . ' ' . Resource::MATERIAL
        );

        $event = new ActionEvent($kingdom);
        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch('event.action', $event);

        return new JsonResponse([
            'data' => [
                'civilian_queues' => $civilianQueues,
                'material_queues' => $materialQueues,
            ],
        ]);
    }

    /**
     * @Route("/build", name="api_action_build")
     * @Method("PUT")
     */
    public function buildAction(Request $request)
    {
        if ($this->getUser()->getVacation()) {
            return $this->createErrorJsonResponse('You are on vacation!');
        }

        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $resourceManager = $this->get('cronkd.manager.resource');

        $housingResource = $resourceManager->get(Resource::HOUSING);
        $civilianResource = $resourceManager->get(Resource::CIVILIAN);
        $availableCivilians = $kingdomManager->lookupResource($kingdom, Resource::CIVILIAN);
        $availableMaterials = $kingdomManager->lookupResource($kingdom, Resource::MATERIAL);

        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action');
        }
        if (!$availableMaterials || $quantity > $availableMaterials->getQuantity()) {
            return $this->createErrorJsonResponse('Note enough materials to complete action');
        }

        $policyManager = $this->get('cronkd.manager.policy');
        $queueLength = 8;
        if ($policyManager->kingdomHasActivePolicy($kingdom, Policy::ECONOMIST)) {
            $queueLength -= 2;
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $civilianQueues = $queuePopulator->build($kingdom, $civilianResource, $queueLength, $quantity);
        $housingQueues = $queuePopulator->build($kingdom, $housingResource, $queueLength, $quantity);

        $availableMaterials->removeQuantity($quantity);
        $em->persist($availableMaterials);
        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            'Building ' . $quantity . ' ' . Resource::HOUSING
        );

        $event = new ActionEvent($kingdom);
        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch('event.action', $event);

        return new JsonResponse([
            'data' => [
                'civilian_queues' => $civilianQueues,
                'housing_queues' => $housingQueues,
            ],
        ]);
    }

    /**
     * @Route("/train_military", name="api_action_train_military")
     * @Method("PUT")
     */
    public function trainMilitaryAction(Request $request)
    {
        if ($this->getUser()->getVacation()) {
            return $this->createErrorJsonResponse('You are on vacation!');
        }

        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $resourceManager = $this->get('cronkd.manager.resource');

        $militaryResource = $resourceManager->get(Resource::MILITARY);
        $availableCivilians = $kingdomManager->lookupResource($kingdom, Resource::CIVILIAN);
        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action!');
        }

        if ($kingdomManager->isAtMaxPopulation($kingdom)) {
            return $this->createErrorJsonResponse('Cannot train military while housing capacity is insufficient!');
        }

        $policyManager = $this->get('cronkd.manager.policy');
        $queueLength = 8;
        if ($policyManager->kingdomHasActivePolicy($kingdom, Policy::ECONOMIST)) {
            $queueLength += 2;
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $militaryQueues = $queuePopulator->build($kingdom, $militaryResource, $queueLength, $quantity);

        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            'Training ' . $quantity . ' ' . Resource::MILITARY
        );

        $event = new ActionEvent($kingdom);
        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch('event.action', $event);

        return new JsonResponse([
            'data' => [
                'military_queues' => $militaryQueues,
            ],
        ]);
    }

    /**
     * @Route("/train_hacker", name="api_action_train_hacker")
     * @Method("PUT")
     */
    public function trainHackerAction(Request $request)
    {
        if ($this->getUser()->getVacation()) {
            return $this->createErrorJsonResponse('You are on vacation!');
        }

        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $resourceManager = $this->get('cronkd.manager.resource');

        $availableMilitary = $kingdomManager->lookupResource($kingdom, Resource::MILITARY);
        if (!$availableMilitary || $quantity > $availableMilitary->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough military to complete action!');
        }

        if ($kingdomManager->isAtMaxPopulation($kingdom)) {
            return $this->createErrorJsonResponse('Cannot train hackers while housing capacity is insufficient!');
        }

        $policyManager = $this->get('cronkd.manager.policy');
        $queueLength = 8;
        if ($policyManager->kingdomHasActivePolicy($kingdom, Policy::ECONOMIST)) {
            $queueLength += 2;
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $hackerResource = $resourceManager->get(Resource::HACKER);
        $hackerQueues = $queuePopulator->build($kingdom, $hackerResource, $queueLength, $quantity);

        $availableMilitary->removeQuantity($quantity);
        $em->persist($availableMilitary);
        $em->flush();

        $this->get('cronkd.manager.log')->createLog(
            $kingdom,
            Log::TYPE_ACTION,
            'Training ' . $quantity . ' ' . Resource::HACKER
        );

        $event = new ActionEvent($kingdom);
        $eventDispatcher = $this->get('event_dispatcher');
        $eventDispatcher->dispatch('event.action', $event);

        return new JsonResponse([
            'data' => [
                'hacker_queues' => $hackerQueues,
            ],
        ]);
    }
}
