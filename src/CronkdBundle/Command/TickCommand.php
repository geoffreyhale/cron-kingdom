<?php
namespace CronkdBundle\Command;

use CronkdBundle\Entity\World;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TickCommand extends ContainerAwareCommand
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
        $logger = $this->getContainer()->get('logger');
        $worldManager = $this->getContainer()->get('cronkd.manager.world');
        $tickService = $this->getContainer()->get('cronkd.tick');

        $worldManager->deactivateExpiringWorlds();
        $worlds = $em->getRepository(World::class)->findByActive(true);

        $logger->info('Starting tick command');

        /** @var World $world */
        foreach ($worlds as $world) {
            $tickService->performTick($world);
        }

        $worldManager->activateUpcomingWorlds();

        $logger->info('Completed command');
    }
}
