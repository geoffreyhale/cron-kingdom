<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\WorldTickEvent;
use CronkdBundle\Exceptions\InvalidWorldSettingsException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LumberMill;
use CronkdBundle\Manager\PolicyManager;
use CronkdBundle\Manager\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class TickService
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var KingdomManager  */
    private $kingdomManager;
    /** @var ResourceManager  */
    private $resourceManager;
    /** @var LumberMill  */
    private $logManager;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var LoggerInterface  */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager,
        LumberMill $logManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->em              = $em;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
        $this->logManager      = $logManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
    }

    /**
     * @param World $world
     * @throws InvalidWorldSettingsException
     */
    public function attemptTick(World $world)
    {
        if (!$world->isActive()) {
            $this->logger->info($world->getName() . " world is not active");
            return;
        }

        $baseResource = $world->getBaseResource();
        if (null === $baseResource) {
            $this->logger->critical('No base population resource is configured!');
            throw new InvalidWorldSettingsException('No base population resource is configured!');
        }

        $this->logger->notice('World ' . $world->getName() . ' starting tick ' . ($world->getTick()+1));

        $queues = $this->em->getRepository(Queue::class)->findNextByWorld($world);
        $this->logger->info('Found ' . count($queues) . ' queues to parse');
        
        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $this->logger->info('Queue is for Kingdom ' . $queue->getKingdom()->getName() . ' for ' . $queue->getResource()->getName());

            $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($queue->getKingdom(), $queue->getResource());
            $quantity = $queue->getQuantity();
            $kingdomResource->addQuantity($quantity);
            $this->em->persist($kingdomResource);
            
            if (0 < $quantity) {
                $this->logManager->logDequeueResource($queue);
            }
            $this->logger->info('Adding ' . $quantity . ' ' . $queue->getResource()->getName() . '; New balance is ' . $kingdomResource->getQuantity());
        }

        foreach ($world->getKingdoms() as $kingdom) {
            $this->kingdomManager->syncResources($kingdom);
            if (!$this->kingdomManager->isAtMaxPopulation($kingdom)) {
                $addition = $this->kingdomManager->incrementPopulation($kingdom);
                $this->logManager->logBirthEvent($kingdom, $baseResource, $addition);
                $this->logger->info($kingdom->getName() . ' kingdom is not at capacity, adding ' . $addition . ' to population');
            } else {
                $this->logger->info($kingdom->getName() . ' is at capacity');
            }
        }

        $world->performTick();
        $this->em->persist($world);
        $this->em->flush();

        $event = new WorldTickEvent($world);
        $this->eventDispatcher->dispatch('event.world_tick', $event);
        $this->logger->notice('World ' . $world->getName() . ' completed tick ' . $world->getTick());
    }
}