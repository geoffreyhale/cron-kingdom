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
        $em           = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $logger       = $this->getContainer()->get('logger');
        $worldManager = $this->getContainer()->get('cronkd.manager.world');
        $tickService  = $this->getContainer()->get('cronkd.tick');

        $worlds = $em->getRepository(World::class)->findActiveWorlds(true);

        $logger->info('Starting tick command');

        /** @var World $world */
        foreach ($worlds as $world) {
            $tickService->attemptTick($world);
        }

        $worldManager->initializeUpcomingWorlds();

        $logger->info('Completed command');
    }
}
