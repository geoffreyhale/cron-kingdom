<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\WorldTickEvent;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LogManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TickService
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var KingdomManager  */
    private $kingdomManager;
    /** @var LogManager  */
    private $logManager;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var LoggerInterface  */
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        LogManager $logManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->em              = $em;
        $this->kingdomManager  = $kingdomManager;
        $this->logManager      = $logManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
    }

    /**
     * @param World $world
     */
    public function performTick(World $world)
    {
        $this->logger->info('Starting world ' . $world->getName() . ': tick ' . $world->getTick());

        $queues = $this->em->getRepository(Queue::class)->findNextByWorld($world);
        $this->logger->info('Found ' . count($queues) . ' queues to parse');

        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $this->logger->info('Queue is for Kingdom ' . $queue->getKingdom()->getName() . ' for ' . $queue->getResource()->getName());

            $kingdomResource = $this->kingdomManager->findOrCreateResource($queue->getKingdom(), $queue->getResource());
            $kingdomResource->addQuantity($queue->getQuantity());
            $this->em->persist($kingdomResource);

            if (0 < $queue->getQuantity()) {
                $this->logManager->createLog(
                    $queue->getKingdom(),
                    Log::TYPE_TICK,
                    $queue->getQuantity() . ' ' . $queue->getResource()->getName() . ' are now available'
                );
            }
            $this->logger->info('Adding ' . $queue->getQuantity() . ' ' . $queue->getResource()->getName() . '; New balance is ' . $kingdomResource->getQuantity());
        }

        $this->logger->info('Completed queues');

        foreach ($world->getKingdoms() as $kingdom) {
            if (!$this->kingdomManager->isAtMaxPopulation($kingdom)) {
                $addition = $this->kingdomManager->incrementPopulation($kingdom);
                $this->logManager->createLog(
                    $kingdom,
                    Log::TYPE_TICK,
                    'Gave birth to ' . $addition . ' ' . Resource::CIVILIAN
                );
                $this->logger->info($kingdom->getName() . ' kingdom is not at capacity, adding ' . $addition . ' to population');
            } else {
                $this->logger->info($kingdom->getName() . ' is at capacity');
            }
        }

        $world->addTick();
        $this->em->persist($world);
        $this->em->flush();

        $event = new WorldTickEvent($world);
        $this->eventDispatcher->dispatch('event.world_tick', $event);
        $this->logger->info('Completed tick ' . $world->getTick() . ' for world ' . $world->getName());
    }
}