<?php
namespace CronkdBundle\Command;

use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\ActivateWorldEvent;
use CronkdBundle\Event\WorldTickEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronkdTickCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cronkd:tick')
            ->setDescription('Perform a tick')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $kingdomManager = $this->getContainer()->get('cronkd.manager.kingdom');
        $logger = $this->getContainer()->get('logger');
        $logManager = $this->getContainer()->get('cronkd.manager.log');

        $this->deactivateExpiringWorlds();
        $worlds = $em->getRepository(World::class)->findByActive(true);

        $logger->info('Starting tick command');

        /** @var World $world */
        foreach ($worlds as $world) {
            $logger->info('Starting world ' . $world->getName() . ': tick ' . $world->getTick());

            $queues = $em->getRepository(Queue::class)->findNextByWorld($world);
            $logger->info('Found ' . count($queues) . ' queues to parse');

            /** @var Queue $queue */
            foreach ($queues as $queue) {
                $logger->info('Queue is for Kingdom ' . $queue->getKingdom()->getName() . ' for ' . $queue->getResource()->getName());

                $kingdomResource = $kingdomManager->findOrCreateResource($queue->getKingdom(), $queue->getResource());
                $kingdomResource->addQuantity($queue->getQuantity());
                $em->persist($kingdomResource);

                if (0 < $queue->getQuantity()) {
                    $logManager->createLog(
                        $queue->getKingdom(),
                        Log::TYPE_TICK,
                        $queue->getQuantity() . ' ' . $queue->getResource()->getName() . ' are now available'
                    );
                }
                $logger->info('Adding ' . $queue->getQuantity() . ' ' . $queue->getResource()->getName() . '; New balance is ' . $kingdomResource->getQuantity());
            }

            $logger->info('Completed queues');

            foreach ($world->getKingdoms() as $kingdom) {
                if (!$kingdomManager->isAtMaxPopulation($kingdom)) {
                    $addition = $kingdomManager->incrementPopulation($kingdom);
                    $logManager->createLog(
                        $kingdom,
                        Log::TYPE_TICK,
                        'Gave birth to ' . $addition . ' ' . Resource::CIVILIAN
                    );
                    $logger->info($kingdom->getName() . ' kingdom is not at capacity, adding ' . $addition . ' to population');
                } else {
                    $logger->info($kingdom->getName() . ' is at capacity');
                }
            }

            $world->addTick();
            $em->persist($world);
            $em->flush();

            $event = new WorldTickEvent($world);
            $eventDispatcher->dispatch('event.world_tick', $event);
            $logger->info('Completed tick ' . $world->getTick() . ' for world ' . $world->getName());
        }

        $this->activateUpcomingWorlds();

        $logger->info('Completed command');
    }

    protected function deactivateExpiringWorlds()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $logger = $this->getContainer()->get('logger');

        $worlds = $em->getRepository(World::class)->findAll();
        /** @var World $world */
        foreach ($worlds as $world) {
            if ($world->shouldBeDeactivated()) {
                $logger->info('Deactivating ' . $world->getName());
                $world->setActive(false);
            }
            $em->persist($world);
        }

        $em->flush();
    }

    protected function activateUpcomingWorlds()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $logger = $this->getContainer()->get('logger');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $worlds = $em->getRepository(World::class)->findAll();
        /** @var World $world */
        foreach ($worlds as $world) {
            if ($world->shouldBeActivated()) {
                $logger->info('Activating ' . $world->getName());
                $world->setActive(true);

                $event = new ActivateWorldEvent($world);
                $eventDispatcher->dispatch('event.activate_world', $event);
            }
            $em->persist($world);
        }

        $em->flush();
    }
}
