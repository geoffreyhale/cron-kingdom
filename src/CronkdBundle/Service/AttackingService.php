<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\AttackLog;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Exceptions\NotEnoughResourcesException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LogManager;
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
    /** @var LogManager */
    private $logManager;
    /** @var PolicyManager */
    private $policyManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        QueuePopulator $queuePopulator,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager,
        LogManager $logManager,
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
     */
    public function attack(Kingdom $kingdom, Kingdom $targetKingdom, array $attackers)
    {
        $attackPower = $this->getArmyAttackPower($kingdom, $attackers);
        $defendingPower = $this->getArmyDefensePower($targetKingdom);
        $result = $attackPower > $defendingPower;
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

        $this->applyDeath($report, $kingdom, $targetKingdom);

        if ($result) {
            $this->awardResources($report, $kingdom, $targetKingdom);
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

            $this->logManager->createLog(
                $kingdom,
                Log::TYPE_ATTACK,
                'Attack queued ' . $quantity . ' ' . $resourceName
            );

            $this->logAttackResult($report, $kingdom, $targetKingdom);
        }
        $this->em->flush();

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
        $previousAttacks = $this->em->getRepository(AttackLog::class)->findOneBy([
            'attacker' => $kingdom,
            'tick'     => $kingdom->getWorld()->getTick(),
        ]);

        return count($previousAttacks);
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
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
            $percentage = $percentage + $attackerPercentage - $defenderPercentage;

            if ($percentage > 0) {
                $this->awardResource($report, $kingdom, $targetKingdom, $resource->getName(), $percentage);
            }
        }
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

        $quantity = ceil($targetKingdomResource->getQuantity() * $percent / 100);
        $this->kingdomManager->modifyResources($kingdom, $resource, $quantity);
        $this->kingdomManager->modifyResources($targetKingdom, $resource, -1 * $quantity);
        $report->addModifiedResource($kingdom, $resource, $quantity);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            "Attack awarded $quantity $resourceName"
        );
        $this->logManager->createLog(
            $targetKingdom,
            Log::TYPE_ATTACK,
            "Attack lost $quantity $resourceName"
        );
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

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $targetKingdom
     * @return AttackLog
     */
    private function logAttackResult(AttackReport $report, Kingdom $kingdom, Kingdom $targetKingdom)
    {
        $attackLog = new AttackLog();
        $attackLog->setAttacker($kingdom);
        $attackLog->setDefender($targetKingdom);
        $attackLog->setTick($kingdom->getWorld()->getTick());
        $attackLog->setSuccess($report->getResult());
        $this->em->persist($attackLog);

        return $attackLog;
    }
}