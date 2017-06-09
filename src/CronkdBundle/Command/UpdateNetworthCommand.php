<?php
namespace CronkdBundle\Command;

use CronkdBundle\Entity\World;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNetworthCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cronkd:networth')
            ->setDescription('Update networth')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $kingdomManager = $this->getContainer()->get('cronkd.manager.kingdom');
        $logger = $this->getContainer()->get('logger');

        $worlds = $em->getRepository(World::class)->findByInitialized(true);

        $logger->info('Starting update networth command');

        /** @var World $world */
        foreach ($worlds as $world) {
            if ($world->isInactive()) {
                continue;
            }

            $logger->info('Update networth for world ' . $world->getName());

            foreach ($world->getKingdoms() as $kingdom) {
                $kingdomManager->calculateNetWorth($kingdom);
            }

            $em->persist($world);
            $em->flush();

            $logger->info('Completed networth update for world ' . $world->getName());
        }

        $logger->info('Completed command');
    }
}
