<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LogManager;
use CronkdBundle\Manager\ResourceManager;
use CronkdBundle\Model\Army;
use CronkdBundle\Model\AttackReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttackingService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var  QueuePopulator */
    private $queuePopulator;
    /** @var  KingdomManager */
    private $kingdomManager;
    /** @var ResourceManager  */
    private $resourceManager;
    /** @var LogManager */
    private $logManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        QueuePopulator $queuePopulator,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager,
        LogManager $logManager
    ) {
        $this->em              = $em;
        $this->eventDispatcher = $dispatcher;
        $this->queuePopulator  = $queuePopulator;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
        $this->logManager      = $logManager;
    }

    /**
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     * @param Army $attackers
     * @return AttackReport
     */
    public function attack(Kingdom $kingdom, Kingdom $targetKingdom, Army $attackers)
    {
        $defendingArmy = $this->getDefendingArmy($targetKingdom);

        $result = $attackers->compare($defendingArmy);
        $report = new AttackReport($kingdom, $targetKingdom, $result);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            ($report->getResult() ? 'Successful' : 'Failed') . ' attack against ' . $targetKingdom->getName()
        );
        $this->logManager->createLog(
            $targetKingdom,
            Log::TYPE_ATTACK,
            ($report->getResult() ? 'Failed' : 'Successful') . ' defend against ' . $kingdom->getName(),
            true
        );

        if (1 == $result) {
            $this->awardResources($report, $kingdom, $targetKingdom);
        }

        foreach ($attackers->getAllTypesOfUnits() as $resourceName) {
            $resource = $this->resourceManager->get($resourceName);
            $queue = $this->queuePopulator->lump($kingdom, $resource, 8, $attackers->getQuantityOfUnit($resourceName));
            $report->addQueue($resource, $queue);

            $kingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
                'kingdom'  => $kingdom,
                'resource' => $resource,
            ]);
            $kingdomResource->removeQuantity($attackers->getQuantityOfUnit($resourceName));
            $this->em->persist($kingdomResource);

            $this->logManager->createLog(
                $kingdom,
                Log::TYPE_ATTACK,
                'Attack queued ' . $attackers->getQuantityOfUnit($resourceName) . ' ' . $resourceName
            );
        }
        $this->em->flush();

        $event = new AttackEvent($kingdom, $targetKingdom);
        $this->eventDispatcher->dispatch('event.attack', $event);

        return $report;
    }

    /**
     * @param Kingdom $kingdom
     * @param array $resources
     * @return Army
     */
    public function buildArmy(Kingdom $kingdom, array $resources)
    {
        $army = new Army($kingdom);
        foreach ($resources as $resourceName => $quantity) {
            $resource = $this->resourceManager->get($resourceName);
            $army->addResource($resource, $quantity);
        }

        return $army;
    }

    /**
     * @param Army $army
     * @return bool
     */
    public function kingdomHasResourcesToAttack(Army $army)
    {
        foreach ($army->getAllTypesOfUnits() as $resourceName) {
            $kingdomResource = $this->kingdomManager->lookupResource($army->getKingdom(), $resourceName);
            if (!$army->hasEnoughToSend($kingdomResource)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Kingdom $kingdom
     * @return Army
     */
    private function getDefendingArmy(Kingdom $kingdom)
    {
        $militaryResources = [
            Resource::MILITARY,
        ];

        $kingdomResources = $this->em->getRepository(KingdomResource::class)
            ->findSpecificResources($kingdom, $militaryResources);

        $resourceMap = [];
        foreach ($kingdomResources as $kingdomResource) {
            $resourceMap[$kingdomResource->getResource()->getName()] = $kingdomResource->getQuantity();
        }

        $army = $this->buildArmy($kingdom, $resourceMap);

        return $army;
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     */
    private function awardResources(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom)
    {
        $this->awardResource($report, $kingdom, $targetKingdom, Resource::CIVILIAN, 20);
        $this->awardResource($report, $kingdom, $targetKingdom, Resource::MATERIAL, 50);
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     */
    private function awardResource(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom, string $resourceName, int $percent)
    {
        $resource = $this->resourceManager->get($resourceName);
        $targetKingdomResource = $this->kingdomManager->lookupResource($targetKingdom, $resourceName);

        $resourceToTransfer = ceil($targetKingdomResource->getQuantity() * $percent / 100);
        $this->kingdomManager->modifyResources($kingdom, $resource, $resourceToTransfer);
        $this->kingdomManager->modifyResources($targetKingdom, $resource, -1 * $resourceToTransfer);
        $report->addModifiedResource($kingdom, $resource, $resourceToTransfer);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            "Attack awarded $resourceToTransfer $resourceName"
        );
        $this->logManager->createLog(
            $targetKingdom,
            Log::TYPE_ATTACK,
            "Attack lost $resourceToTransfer $resourceName"
        );
    }
}