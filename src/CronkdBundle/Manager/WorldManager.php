<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Tech\Policy;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\InitializeWorldEvent;
use CronkdBundle\Model\WorldState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorldManager
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var  KingdomManager */
    private $kingdomManager;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var LoggerInterface  */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->em              = $em;
        $this->kingdomManager  = $kingdomManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param World $world
     * @return World
     */
    public function create(World $world)
    {
        $this->em->persist($world);
        $this->em->flush();

        return $world;
    }

    /**
     * @param World $world
     * @return World
     */
    public function update(World $world)
    {
        $this->em->persist($world);
        $this->em->flush();

        return $world;
    }

    /**
     * @param World $world
     * @return WorldState
     */
    public function generateWorldState(World $world)
    {
        $policies = $this->em->getRepository(Policy::class)->findAll();

        $worldState = new WorldState($world, $policies);
        $worldState
            ->setAggregateNetWorth($this->calculateWorldNetWorth($world))
            ->setKingdomsByElo($this->kingdomManager->calculateKingdomsByElo($world))
            ->setKingdomsByNetWorth($this->kingdomManager->calculateKingdomsByNetWorth($world))
            ->setKingdomsByWinLossRecord($this->kingdomManager->calculateKingdomsByWinLoss($world))
        ;

        return $worldState;
    }

    /**
     * @param World $world
     * @return int
     */
    public function calculateWorldNetWorth(World $world)
    {
        $worldNetWorth = 0;
        foreach ($world->getKingdoms() as $kingdom) {
            $worldNetWorth += $kingdom->getNetworth();
        }

        return $worldNetWorth;
    }

    public function initializeUpcomingWorlds()
    {
        $worlds = $this->em->getRepository(World::class)->findAll();

        /** @var World $world */
        foreach ($worlds as $world) {
            if ($world->shouldBeInitialized()) {
                $this->logger->info('Initializing world ' . $world->getName());
                $world->setInitialized(true);

                $event = new InitializeWorldEvent($world);
                $this->eventDispatcher->dispatch('event.initialize_world', $event);
            }
            $this->em->persist($world);
        }

        $this->em->flush();
    }
}