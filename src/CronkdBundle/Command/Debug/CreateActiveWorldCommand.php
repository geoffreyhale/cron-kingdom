<?php
namespace CronkdBundle\Command\Debug;

use CronkdBundle\Entity\World;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateActiveWorldCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cronkd:debug:create-world')
            ->setDescription('Create world for debug purposes')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the world to create')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $world = new World();
        $world->setName($input->getArgument('name'));
        $world->setTick(1);
        $world->setActive(false);
        $world->setStartTime(new \DateTime('2016-01-01'));
        $world->setEndTime(new \DateTime('2020-12-31'));
        $em->persist($world);
        $em->flush();

        $output->writeln('Created world <comment>' . $world->getName() . '</comment>');
    }
}
