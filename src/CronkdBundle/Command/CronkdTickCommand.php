<?php
namespace CronkdBundle\Command;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
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
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $kingdomManager = $this->getContainer()->get('cronkd.manager.kingdom');
        $logger = $this->getContainer()->get('logger');
        $logManager = $this->getContainer()->get('cronkd.manager.log');
        $worlds = $em->getRepository(World::class)->findAll();

        $logger->info('Starting tick command');

        /** @var World $world */
        foreach ($worlds as $world) {
            $logger->info('Starting world ' . $world->getName() . ': tick ' . $world->getTick());

            $queues = $em->getRepository(Queue::class)->findNextByWorld($world);
            $logger->info('Found ' . count($queues) . ' queues to parse');

            /** @var Queue $queue */
            foreach ($queues as $queue) {
                $logger->info('Queue is for Kingdom ' . $queue->getKingdom()->getName() . ' for ' . $queue->getResource()->getName());

                /** @var KingdomResource $kingdomResource */
                $kingdomResource = $em->getRepository(KingdomResource::class)->findOneBy([
                    'kingdom'  => $queue->getKingdom(),
                    'resource' => $queue->getResource(),
                ]);
                if (!$kingdomResource) {
                    $logger->info('Creating new KingdomResource entity');

                    $kingdomResource = new KingdomResource();
                    $kingdomResource->setQuantity(0);
                    $kingdomResource->setKingdom($queue->getKingdom());
                    $kingdomResource->setResource($queue->getResource());
                    $em->persist($kingdomResource);
                }

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

                $kingdomManager->calculateNetWorth($kingdom);
                $logger->info($kingdom->getName() . ' kingdom has a net worth of ' . $kingdom->getNetWorth());
            }

            $logger->info('Completed tick ' . $world->getTick());

            $world->addTick();
            $em->persist($world);
        }

        $em->flush();
        $logger->info('Completed command');
    }
}
