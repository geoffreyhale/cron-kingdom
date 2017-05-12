<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\AttackLog;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Policy;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\ResourceType;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Exceptions\NotEnoughResourcesException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LogManager;
use CronkdBundle\Manager\PolicyManager;
use CronkdBundle\Manager\ResourceManager;
use CronkdBundle\Model\Army;
use CronkdBundle\Model\AttackReport;
use CronkdBundle\Model\KingdomState;
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
        PolicyManager $policyManager,
        array $settings
    ) {
        $this->em              = $em;
        $this->eventDispatcher = $dispatcher;
        $this->queuePopulator  = $queuePopulator;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
        $this->logManager      = $logManager;
        $this->policyManager   = $policyManager;
        $this->settings        = $settings;
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

        $event = new AttackEvent($kingdom, $targetKingdom);
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

            $attackPower += ($quantity * $kingdomResource->getResource()->getAttack());
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
        $defendingPower = 0;
        foreach ($this->settings['resources'] as $resourceName => $resourceData) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            if (null === $resource) {
                throw new InvalidResourceException($resourceName);
            }

            if ($resourceData['defense'] > 0) {
                $kingdomResource = $this->kingdomManager->lookupResource($kingdom, $resourceName);
                $defendingPower += ($resourceData['defense'] * $kingdomResource->getQuantity());
            }
        }

        $kingdomState = $this->kingdomManager->generateKingdomState($kingdom);
        if (Policy::DEFENDER == $kingdomState->getActivePolicyName()) {
            $defendingPower *= Policy::DEFENDER_BONUS;
        }

        return $defendingPower;
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
        $kingdomState = $this->kingdomManager->generateKingdomState($kingdom);
        $housingPercentage = 1;
        if ($kingdomState->getActivePolicyName() == Policy::WARMONGER) {
            $housingPercentage *= Policy::WARMONGER_BONUS;
        }

        foreach ($this->settings['resources'] as $resourceName => $resourceData) {
            $percentage = 0;
            if ($resourceData['spoil_of_war']) {
                switch ($resourceData['type']) {
                    case ResourceType::BUILDING:
                        $percentage = $housingPercentage;
                        break;
                    case ResourceType::MATERIAL:
                        $percentage = 50;
                        break;
                    case ResourceType::POPULATION:
                        $percentage = 20;
                        break;
                }

                $this->awardResource($report, $kingdom, $targetKingdom, $resourceName, $percentage);
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