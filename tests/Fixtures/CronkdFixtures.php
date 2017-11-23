<?php
namespace Tests\Fixtures;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Entity\World;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CronkdFixtures extends Fixture
{
    /** @var array */
    private $resources = [
        'Civilian' => [
            'type'                 => 'Population',
            'attack'               => 0,
            'defense'              => 0,
            'starting'             => 100,
            'can_be_produced'      => false,
            'can_spoil_of_war'     => false,
            'can_be_probed'        => true,
            'capacity'             => 0,
            'spoil_of_war_percent' => 0,
            'value'                => 1,
        ],
        'Soldier' => [
            'type'                 => 'Population',
            'attack'               => 1,
            'defense'              => 1,
            'starting'             => 0,
            'can_be_produced'      => true,
            'can_spoil_of_war'     => false,
            'can_be_probed'        => true,
            'capacity'             => 0,
            'spoil_of_war_percent' => 0,
            'value'                => 1,
        ],
        'Attacker' => [
            'type'                 => 'Population',
            'attack'               => 1,
            'defense'              => 0,
            'starting'             => 0,
            'can_be_produced'      => true,
            'can_spoil_of_war'     => false,
            'can_be_probed'        => true,
            'capacity'             => 0,
            'spoil_of_war_percent' => 0,
            'value'                => 1,
        ],
        'Defender' => [
            'type'                 => 'Population',
            'attack'               => 0,
            'defense'              => 1,
            'starting'             => 0,
            'can_be_produced'      => true,
            'can_spoil_of_war'     => false,
            'can_be_probed'        => true,
            'capacity'             => 0,
            'spoil_of_war_percent' => 0,
            'value'                => 1,
        ],
    ];

    public function load(ObjectManager $em)
    {
        $this->generateResourceTypes($em);

        $world = $this->generateWorld($em);
        $kingdoms = $this->generateKingdoms($em, ['Hero', 'Villain'], $world);
        $resources = $this->generateResources($em, $world);
    }

    private function generateResourceTypes(ObjectManager $em)
    {
        $resourceTypes = [];
        $resourceTypeNames = ['Population', 'Material', 'Building'];
        foreach ($resourceTypeNames as $resourceTypeName) {
            $resourceType = new ResourceType();
            $resourceType->setName($resourceTypeName);
            $em->persist($resourceType);
            $resourceTypes[] = $resourceType;
        }
        $em->flush();

        return $resourceTypes;
    }

    private function generateWorld(ObjectManager $em)
    {
        $world = new World();
        $world->setName('World');
        $world->setTick(1);
        $world->setStartTime((new \DateTime())->sub(new \DateInterval('P1M')));
        $world->setEndTime((new \DateTime())->add(new \DateInterval('P1M')));
        $world->setTickInterval(1);
        $world->setBirthRate(1);

        $em->persist($world);
        $em->flush();

        return $world;
    }

    private function generateResources(ObjectManager $em, World $world)
    {
        $resources = [];
        foreach ($this->resources as $name => $resourceData) {
            $resource = new Resource();
            $resource->setName($name);
            $resource->setType($em->getRepository(ResourceType::class)->findOneByName($resourceData['type']));
            $resource->setWorld($world);
            $resource->setDescription($name);
            $resource->setStartingAmount($resourceData['starting']);
            $resource->setAttack($resourceData['attack']);
            $resource->setDefense($resourceData['defense']);
            $resource->setCanBeProbed($resourceData['can_be_probed']);
            $resource->setCanBeProduced($resourceData['can_be_produced']);
            $resource->setSpoilOfWar($resourceData['can_spoil_of_war']);
            $resource->setCapacity($resourceData['capacity']);
            $resource->setSpoilOfWarCapturePercentage($resourceData['spoil_of_war_percent']);
            $resource->setValue($resourceData['value']);
            $em->persist($resource);
            $resources[] = $resource;
        }
        $em->flush();

        return $resources;
    }

    private function generateKingdoms(ObjectManager $em, array $names, World $world)
    {
        $kingdoms = [];
        foreach ($names as $name) {
            $kingdom = new Kingdom();
            $kingdom->setName($name);
            $kingdom->setWorld($world);
            $kingdom->setNetWorth(0);
            $kingdom->setLiquidity(0);
            $em->persist($kingdom);
            $kingdoms[] = $kingdom;
        }
        $em->flush();

        return $kingdoms;
    }
}