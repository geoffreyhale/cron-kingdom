<?php
namespace CronkdBundle\Command;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cronkd:init')
            ->setDescription('Populate entities based on configuration')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $settings = $this->getContainer()->getParameter('cronkd.settings');
        $resourceTypes = $settings['resource_types'];
        $resources = $settings['resources'];

        foreach ($resourceTypes as $resourceType) {
            $output->writeln("Loaded <comment>$resourceType</comment> resource type");
            $typeExists = $em->getRepository(ResourceType::class)->findOneByName($resourceType);
            if (!$typeExists) {
                $type = new ResourceType();
                $type->setName($resourceType);
                $em->persist($type);
            }
        }
        $em->flush();

        $resourceTypes = $em->getRepository(ResourceType::class)->findAll();
        $resourceTypesByName = [];
        foreach ($resourceTypes as $resourceType) {
            $resourceTypesByName[$resourceType->getName()] = $resourceType;
        }

        foreach ($resources as $name => $resourceConf) {
            $output->writeln("Loaded <comment>$name</comment> resource");

            if (!isset($resourceTypesByName[$resourceConf['type']])) {
                $output->writeln('Error: cannot find resource type for reosource ' . $name);
            }

            $resource = $em->getRepository(Resource::class)->findOneByName($name);
            if (!$resource) {
                $resource = new Resource();
                $resource->setName($name);
            }

            $resource->setType($resourceTypesByName[$resourceConf['type']]);
            $resource->setCanBeProduced(isset($resourceConf['action']['verb']));
            $resource->setValue($resourceConf['value']);
            $resource->setAttack($resourceConf['attack']);
            $resource->setDefense($resourceConf['defense']);
            $resource->setCapacity($resourceConf['capacity']);
            $resource->setCanBeProbed($resourceConf['can_be_probed']);
            $resource->setProbePower($resourceConf['probe_power']);
            $em->persist($resource);
        }
        $em->flush();
    }
}
