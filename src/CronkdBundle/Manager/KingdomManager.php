<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\User;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
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
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var NullLogger  */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        ResourceManager $resourceManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em              = $em;
        $this->resourceManager = $resourceManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = new NullLogger();
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
     * @param Resource $resource
     * @return KingdomResource
     */
    public function findOrCreateResource(Kingdom $kingdom, Resource $resource)
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

        return $this->findOrCreateResource($kingdom, $resource);
    }

    /**
     * @param Kingdom $kingdom
     * @return integer
     */
    public function getPopulationCapacity(Kingdom $kingdom)
    {
        $activeHousingResources = $this->em->getRepository(KingdomResource::class)
            ->findSumOfSpecificResources($kingdom, [
                    $this->resourceManager->get(Resource::HOUSING)->getId()
                ]
            );

        return $activeHousingResources;
    }

    /**
     * @param Kingdom $kingdom
     * @return integer
     */
    public function getPopulation(Kingdom $kingdom)
    {
        $civilianResource = $this->resourceManager->get(Resource::CIVILIAN);
        $militaryResource = $this->resourceManager->get(Resource::MILITARY);
        $hackerResource   = $this->resourceManager->get(Resource::HACKER);

        $activePopulationResources = $this->em->getRepository(KingdomResource::class)
            ->findSumOfSpecificResources($kingdom, [
                    $civilianResource->getId(),
                    $militaryResource->getId(),
                    $hackerResource->getId()
                ]
            );

        $inactiveCivilianResources = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $civilianResource);
        $inactiveMilitaryResources = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $militaryResource);
        $inactiveHackerResources   = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $hackerResource);

        $totalPopulation = $activePopulationResources +
            $inactiveCivilianResources +
            $inactiveMilitaryResources +
            $inactiveHackerResources
        ;

        return $totalPopulation;
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
     * @return int
     */
    public function incrementPopulation(Kingdom $kingdom)
    {
        $civilianResource  = $this->resourceManager->get(Resource::CIVILIAN);
        $activeCivilians   = $this->lookupResource($kingdom, Resource::CIVILIAN);
        $inactiveCivilians = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $civilianResource);
        $totalCivilians    = $activeCivilians->getQuantity() + $inactiveCivilians;
        $birthedCivilians  = floor($totalCivilians / 10);
        if (0 == $birthedCivilians) {
            $birthedCivilians = 1;
        }

        $currentPopulation = $this->getPopulation($kingdom);
        $totalCapacity = $this->getPopulationCapacity($kingdom);
        if (($birthedCivilians + $currentPopulation) > $totalCapacity) {
            $birthedCivilians = $this->getPopulationCapacityRemaining($kingdom);
        }

        $activeCivilians->addQuantity($birthedCivilians);
        $this->em->persist($activeCivilians);
        $this->em->flush();

        return $birthedCivilians;
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function calculateNetWorth(Kingdom $kingdom)
    {
        $this->calculateLiquidity($kingdom);
        $netWorth = $kingdom->getLiquidity();

        foreach ($kingdom->getResources() as $kingdomResource) {
            $totalQueued = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $kingdomResource->getResource());
            $this->logger->info($kingdom->getName() . ' net worth ' . $kingdomResource->getResource()->getName() . ' = ' . $totalQueued);
            $netWorth += $totalQueued;
        }

        $kingdom->setNetWorth($netWorth);
        $this->em->persist($kingdom);
        $this->em->flush();
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    private function calculateLiquidity(Kingdom $kingdom)
    {
        $liquidity = 0;

        /** @var KingdomResource $resource */
        foreach ($kingdom->getResources() as $resource) {
            $liquidity += $resource->getQuantity() * $resource->getResource()->getValue();
        }

        $kingdom->setLiquidity($liquidity);
        $this->em->persist($kingdom);
        $this->em->flush();

        return $liquidity;
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

        $kingdomResource = $this->findOrCreateResource($kingdom, $resource);
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
    public function calculateKingdomsByNetWorth(World $world)
    {
        $kingdomsByNetWorth = $world->getKingdoms()->toArray();
        usort($kingdomsByNetWorth, function ($item1, $item2) {
            return $item2->getNetworth() <=> $item1->getNetworth();
        });

        return $kingdomsByNetWorth;
    }
}