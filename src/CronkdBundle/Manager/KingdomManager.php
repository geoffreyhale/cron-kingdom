<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Notification\Notification;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Entity\User;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ResetKingdomEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Model\KingdomState;
use CronkdBundle\Service\ResourceActionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class KingdomManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var ResourceManager  */
    private $resourceManager;
    /** @var PolicyManager  */
    private $policyManager;
    /** @var ResourceActionService */
    private $resourceActionService;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var NullLogger  */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        ResourceManager $resourceManager,
        PolicyManager $policyManager,
        ResourceActionService $resourceActionService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em                    = $em;
        $this->resourceManager       = $resourceManager;
        $this->policyManager         = $policyManager;
        $this->resourceActionService = $resourceActionService;
        $this->eventDispatcher       = $eventDispatcher;
        $this->logger                = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     * @return KingdomManager
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Kingdom $kingdom
     * @param World $world
     * @param User $user
     * @return Kingdom
     */
    public function createKingdom(Kingdom $kingdom, World $world, User $user)
    {
        $kingdom->setWorld($world);
        $kingdom->setUser($user);

        $this->em->persist($kingdom);
        $this->em->flush();

        $event = new CreateKingdomEvent($kingdom);
        $this->eventDispatcher->dispatch('event.create_kingdom', $event);

        return $kingdom;
    }

    /**
     * @param Kingdom $kingdom
     * @return KingdomState
     */
    public function generateKingdomState(Kingdom $kingdom)
    {
        $kingdomState = new KingdomState($kingdom, $this->resourceActionService);
        $winLossRecord = $this->em->getRepository(AttackResultEvent::class)->getWinLossRecord($kingdom);
        $kingdomState
            ->setWinLossRecord($winLossRecord['win'], $winLossRecord['loss'])
            ->setCurrentQueues($this->getResourceQueues($kingdom))
            ->setNotificationCount($this->em->getRepository(Notification::class)->findNotificationCount($kingdom))
            ->setAvailableAttack($this->em->getRepository(AttackResultEvent::class)->hasAvailableAttack($kingdom))
        ;

        return $kingdomState;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return KingdomResource
     */
    public function findOrCreateKingdomResource(Kingdom $kingdom, Resource $resource)
    {
        $kingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $resource,
        ]);

        if (!$kingdomResource) {
            $kingdomResource = new KingdomResource();
            $kingdomResource->setKingdom($kingdom);
            $kingdomResource->setResource($resource);
            $kingdomResource->setQuantity(0);
        }
        $this->em->persist($kingdomResource);

        return $kingdomResource;
    }

    /**
     * @param Kingdom $kingdom
     * @param string $resourceName
     * @return KingdomResource
     * @throws InvalidResourceException
     */
    public function lookupResource(Kingdom $kingdom, string $resourceName)
    {
        $resource = $this->resourceManager->get($resourceName);
        if (!$resource) {
            throw new InvalidResourceException($resourceName);
        }

        return $this->findOrCreateKingdomResource($kingdom, $resource);
    }

    /**
     * @param Kingdom $kingdom
     * @return integer
     */
    public function getPopulationCapacity(Kingdom $kingdom)
    {
        $capacity = 0;
        $housingResources = $this->resourceManager->getBuildingResources();
        foreach ($housingResources as $resource) {
            $kingdomResourceSum = $this->em->getRepository(KingdomResource::class)
                ->findSumOfSpecificResources($kingdom, [$resource->getId()]);
            $resourceCapacity = ($kingdomResourceSum * $resource->getCapacity());
            $capacityMultiplier = $this->policyManager->calculateCapacityMultiplier($kingdom, $resource);
            $capacity += floor($resourceCapacity * $capacityMultiplier);
        }

        return $capacity;
    }

    /**
     * @param Kingdom $kingdom
     * @return integer
     */
    public function getPopulation(Kingdom $kingdom)
    {
        $populationResources = $this->resourceManager->getPopulationResources();
        $resourceIds = [];
        $inactivePopulation = 0;
        foreach ($populationResources as $resource) {
            $resourceIds[] = $resource->getId();
            $inactivePopulation += $this->em->getRepository(Queue::class)
                ->findTotalQueued($kingdom, $resource);
        }

        $activePopulation = $this->em->getRepository(KingdomResource::class)
            ->findSumOfSpecificResources($kingdom, $resourceIds);

        return $activePopulation + $inactivePopulation;
    }

    /**
     * @param Kingdom $kingdom
     * @return integer
     */
    public function getPopulationCapacityRemaining(Kingdom $kingdom)
    {
        $totalPopulation = $this->getPopulation($kingdom);
        $activeHousingResources = $this->getPopulationCapacity($kingdom);

        return $activeHousingResources - $totalPopulation;
    }

    /**
     * @param Kingdom $kingdom
     * @return bool
     */
    public function isAtMaxPopulation(Kingdom $kingdom)
    {
        $totalPopulation = $this->getPopulation($kingdom);
        $activeHousingResources = $this->getPopulationCapacity($kingdom);

        return $totalPopulation >= $activeHousingResources;
    }

    /**
     * @param Kingdom $kingdom
     * @return float|int
     */
    public function incrementPopulation(Kingdom $kingdom)
    {
        $currentPopulation = 0;

        foreach ($this->resourceManager->getPopulationResources() as $populationResource) {
            $active = $kingdom->getResource($populationResource)->getQuantity();
            $inactive = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $populationResource);
            $currentPopulation += $active + $inactive;
        }

        $birthedPopulation  = floor($kingdom->getWorld()->getBirthRate() / 100 * $currentPopulation);
        if (0 == $birthedPopulation) {
            $birthedPopulation = 1;
        }

        $currentPopulation = $this->getPopulation($kingdom);
        $totalCapacity = $this->getPopulationCapacity($kingdom);
        if (($birthedPopulation + $currentPopulation) > $totalCapacity) {
            $birthedPopulation = $this->getPopulationCapacityRemaining($kingdom);
        }

        $baseResource  = $kingdom->getWorld()->getBaseResource();
        $activeBaseResource = $kingdom->getResource($baseResource);
        $activeBaseResource->addQuantity($birthedPopulation);
        $this->em->persist($activeBaseResource);
        $this->em->flush();

        return $birthedPopulation;
    }

    /**
     * If a resource is added after the Kingdom has started, add it now.
     *
     * @param Kingdom $kingdom
     */
    public function syncResources(Kingdom $kingdom)
    {
        $resources = $this->resourceManager->getWorldResources($kingdom->getWorld());
        foreach ($resources as $resource) {
            $this->findOrCreateKingdomResource($kingdom, $resource);
        }
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function calculateNetWorth(Kingdom $kingdom, bool $performFlush = false)
    {
        $this->calculateLiquidity($kingdom);
        $netWorth = $kingdom->getLiquidity();

        foreach ($kingdom->getResources() as $kingdomResource) {
            $totalQueued = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $kingdomResource->getResource());
            $netWorth += $totalQueued * $kingdomResource->getResource()->getValue();
        }

        $this->logger->info($kingdom->getName() . ' net worth = ' . $netWorth);

        $kingdom->setNetWorth($netWorth);
        $this->em->persist($kingdom);
        if ($performFlush) {
            $this->em->flush();
        }

        return $netWorth;
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    private function calculateLiquidity(Kingdom $kingdom)
    {
        $liquidity = $this->em->getRepository(KingdomResource::class)->calculateLiquidity($kingdom);

        $kingdom->setLiquidity($liquidity);
        $this->em->persist($kingdom);

        return $liquidity;
    }

    /**
     * @param Kingdom $kingdom
     * @param bool $performFlush
     * @return Kingdom
     */
    public function calculateAttackAndDefense(Kingdom $kingdom, bool $performFlush = false)
    {
        $attack = 0;
        $defense = 0;

        /** @var KingdomResource $resource */
        foreach ($kingdom->getResources() as $resource) {
            $attack += $resource->getQuantity() * $resource->getResource()->getAttack();
            $defense += $resource->getQuantity() * $resource->getResource()->getDefense();
        }

        $kingdom->setAttack($attack);
        $kingdom->setDefense($defense);
        $this->em->persist($kingdom);
        if ($performFlush) {
            $this->em->flush();
        }

        return $kingdom;
    }

    /**
     * @param Kingdom $kingdom
     * @return Queue[]
     */
    public function getResourceQueues(Kingdom $kingdom)
    {
        $queues = [];

        $kingdomResources = $this->em->getRepository(KingdomResource::class)->findByKingdom($kingdom);

        /** @var KingdomResource $kingdomResource */
        foreach ($kingdomResources as $kingdomResource) {
            $queues[] = [
                'kingdomResource' => $kingdomResource,
                'queues'          => $this->em->getRepository(Queue::class)->findCurrentQueues($kingdomResource),
            ];
        }

        return $queues;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param int $quantity
     * @return KingdomResource
     */
    public function modifyResources(Kingdom $kingdom, Resource $resource, int $quantity)
    {
        $this->logger->info('Modifying resource for Kingdom ' . $kingdom->getName() . '; Resource ' . $resource->getName() . '; Qty: ' . $quantity);

        $kingdomResource = $this->findOrCreateKingdomResource($kingdom, $resource);
        $kingdomResource->addQuantity($quantity);
        if (0 > $kingdomResource->getQuantity()) {
            $kingdomResource->setQuantity(0);
        }

        $this->em->persist($kingdomResource);
        $this->em->flush();

        return $kingdomResource;
    }

    /**
     * @param World $world
     * @return Kingdom[]
     */
    public function calculateKingdomsByElo(World $world)
    {
        $kingdomsByElo = $world->getKingdoms()->toArray();
        usort($kingdomsByElo, function ($item1, $item2) {
            return $item2->getElo() <=> $item1->getElo();
        });

        return $kingdomsByElo;
    }

    /**
     * @param World $world
     * @return Kingdom[]
     */
    public function calculateKingdomsByNetWorth(World $world)
    {
        $kingdomsByNetWorth = $world->getKingdoms()->toArray();
        usort($kingdomsByNetWorth, function ($item1, $item2) {
            return $item2->getNetworth() <=> $item1->getNetworth();
        });

        return $kingdomsByNetWorth;
    }

    public function calculateKingdomsByWinLoss(World $world)
    {
        $kingdoms = $world->getKingdoms()->toArray();

        $kingdomsByWinLoss = [];
        foreach($kingdoms as $kingdom) {
            $kingdomsByWinLoss[$kingdom->getId()] = [
                'kingdom' => $kingdom,
                'winloss' => $this->em->getRepository(AttackResultEvent::class)->getWinLossRecord($kingdom)
            ];
        }

        usort($kingdomsByWinLoss, function ($item1, $item2) {
            $item1diff = $item1['winloss']['win'] - $item1['winloss']['loss'];
            $item2diff = $item2['winloss']['win'] - $item2['winloss']['loss'];

            if ($item1diff == $item2diff) {
                return $item2['winloss']['loss'] <=> $item1['winloss']['loss'];
            }
            return $item2diff <=> $item1diff;
        });

        return $kingdomsByWinLoss;
    }

    /**
     * @param Kingdom $kingdom
     */
    public function resetKingdom(Kingdom $kingdom)
    {
        $currentTechPoints = $kingdom->getTechPoints();
        $currentTechPoints += $this->calculateTechPoints($kingdom);
        $kingdom->setTechPoints($currentTechPoints);
        $this->em->persist($kingdom);
        $this->em->flush();

        $event = new ResetKingdomEvent($kingdom);
        $this->eventDispatcher->dispatch('event.reset_kingdom', $event);
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function calculateTechPoints(Kingdom $kingdom)
    {
        $netWorth = $kingdom->getNetWorth();

        $techPoints = (int) ($netWorth/1000 * log($netWorth/10000));
        if ($techPoints < 0) {
            $techPoints = 0;
        }
        return $techPoints;
    }
}