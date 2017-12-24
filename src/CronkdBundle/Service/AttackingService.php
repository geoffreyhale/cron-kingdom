<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Exceptions\InvalidQueueIntervalException;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Exceptions\NotEnoughResourcesException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LumberMill;
use CronkdBundle\Manager\PolicyManager;
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
    /** @var LumberMill */
    private $logManager;
    /** @var PolicyManager */
    private $policyManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        QueuePopulator $queuePopulator,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager,
        LumberMill $logManager,
        PolicyManager $policyManager
    ) {
        $this->em              = $em;
        $this->eventDispatcher = $dispatcher;
        $this->queuePopulator  = $queuePopulator;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
        $this->logManager      = $logManager;
        $this->policyManager   = $policyManager;
    }

    /**
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     * @param array $attackers
     * @return AttackReport
     * @throws InvalidResourceException
     * @throws NotEnoughResourcesException
     * @throws InvalidQueueIntervalException
     */
    public function attack(Kingdom $kingdom, Kingdom $targetKingdom, array $attackers)
    {
        $attackPower = $this->getArmyAttackPower($kingdom, $attackers);
        $defendingPower = $this->getArmyDefensePower($targetKingdom);
        $result = $attackPower > $defendingPower;
        $report = new AttackReport($kingdom, $targetKingdom, $result);

        // tkelleher: Need to figure world logs to do this I think
        //$this->applyDeath($report, $kingdom, $targetKingdom);

        if ($result) {
            $report = $this->awardResources($report, $kingdom, $targetKingdom);
        }

        foreach ($attackers as $resourceName => $quantity) {
            $resource = $this->resourceManager->get($resourceName);
            $queue = $this->queuePopulator->build($kingdom, $resource, 8, $quantity);
            $report->addQueue($resource, $queue);

            $kingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
                'kingdom'  => $kingdom,
                'resource' => $resource,
            ]);
            $kingdomResource->removeQuantity($quantity);
            $this->em->persist($kingdomResource);

            if (0 < $quantity) {
                $this->logManager->logQueueResource($kingdom, $resource, $quantity, false, true);
            }
        }
        $this->em->flush();

        $report = $this->logManager->logAttackResult($kingdom, $targetKingdom, $report);

        $event = new AttackEvent($kingdom, $targetKingdom, $result);
        $this->eventDispatcher->dispatch('event.attack', $event);

        return $report;
    }

    /**
     * @param Kingdom $kingdom
     * @param array $quantities
     * @return int
     * @throws InvalidResourceException
     * @throws NotEnoughResourcesException
     */
    private function getArmyAttackPower(Kingdom $kingdom, array $quantities)
    {
        $attackPower = 0;
        foreach ($quantities as $resourceName => $quantity) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            if (null === $resource) {
                throw new InvalidResourceException($resourceName);
            }

            $kingdomResource = $this->kingdomManager->lookupResource($kingdom, $resourceName);
            if ($kingdomResource->getQuantity() < $quantity) {
                throw new NotEnoughResourcesException($resourceName);
            }

            $resourceAttackPower = ($quantity * $kingdomResource->getResource()->getAttack());
            $attackPowerMultiplier = $this->policyManager->calculateAttackMultiplier($kingdom, $resource);
            $attackPower += ($resourceAttackPower * $attackPowerMultiplier);
        }

        return $attackPower;
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     * @throws InvalidResourceException
     */
    private function getArmyDefensePower(Kingdom $kingdom)
    {
        $defensePower = 0;
        $resources = $this->resourceManager->getWorldResources($kingdom->getWorld());

        /** @var Resource $resource */
        foreach ($resources as $resource) {
            if ($resource->getDefense() > 0) {
                $kingdomResource = $this->kingdomManager->lookupResource($kingdom, $resource->getName());
                $defensePowerMultiplier = $this->policyManager->calculateDefenseMultiplier($kingdom, $resource);
                $resourceDefensePower = ($resource->getDefense() * $kingdomResource->getQuantity());
                $defensePower += ($resourceDefensePower * $defensePowerMultiplier);
            }
        }

        return $defensePower;
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function numAttacksThisTick(Kingdom $kingdom)
    {
        $previousAttacks = $this->em->getRepository(AttackResultEvent::class)->findOneBy([
            'attacker' => $kingdom,
            'tick'     => $kingdom->getWorld()->getTick(),
        ]);

        return count($previousAttacks);
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     * @return AttackReport
     */
    private function awardResources(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom)
    {
        $resources = $this->resourceManager->getWorldResources($kingdom->getWorld());
        /** @var Resource $resource */
        foreach ($resources as $resource) {
            $percentage = 0;
            if ($resource->getSpoilOfWar()) {
                $percentage = $resource->getSpoilOfWarCapturePercentage();
            }

            $attackerPercentage = $this->policyManager->calculateAttackerSpoilOfWarPercentageMultiplier($kingdom, $resource);
            $defenderPercentage = $this->policyManager->calculateDefenderSpoilOfWarPercentageMultiplier($targetKingdom, $resource);
            $percentage = $percentage + $attackerPercentage + $defenderPercentage;

            if ($percentage > 0) {
                $report = $this->awardResource($report, $kingdom, $targetKingdom, $resource->getName(), $percentage);
            }
        }

        return $report;
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     * @param string $resourceName
     * @param int $percent
     * @return AttackReport
     * @throws InvalidResourceException
     */
    private function awardResource(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom, string $resourceName, int $percent)
    {
        $resource = $this->resourceManager->get($resourceName);
        $targetKingdomResource = $this->kingdomManager->lookupResource($targetKingdom, $resourceName);

        $quantity = ceil($targetKingdomResource->getQuantity() * $percent / 100);
        $this->kingdomManager->modifyResources($kingdom, $resource, $quantity);
        $this->kingdomManager->modifyResources($targetKingdom, $resource, -1 * $quantity);
        $report->addModifiedResource($resource, $quantity);

        $this->logManager->logAttackReward($kingdom, $resource, $quantity);
        $this->logManager->logAttackReward($targetKingdom, $resource, -$quantity);

        return $report;
    }

    /**
     * Apply Death
     *
     * Attack loser loses 20% of Civilians
     *
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     */
    private function applyDeath(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom) {
        $resourceName = 'Civilian';
        $resource = $this->resourceManager->get($resourceName);
        $targetKingdomResource = $this->kingdomManager->lookupResource($targetKingdom, $resourceName);
        $kingdomResource = $this->kingdomManager->lookupResource($kingdom, $resourceName);

        // Attacker Win / Defender Lose
        if ($report->getResult()) {
            $this->kingdomManager->modifyResources(
                $targetKingdom,
                $resource,
                -floor($targetKingdomResource->getQuantity() * 20 / 100)
            );
        }

        // Attacker Lose / Defender Win
        if (!$report->getResult()) {
            $this->kingdomManager->modifyResources(
                $kingdom,
                $resource,
                -floor($kingdomResource->getQuantity() * 20 / 100)
            );
        }
    }
}