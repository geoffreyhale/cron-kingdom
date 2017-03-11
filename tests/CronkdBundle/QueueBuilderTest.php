<?php

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Service\QueueBuilder;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QueueBuilderTest extends KernelTestCase
{
    /** @var  ContainerInterface */
    private $container;
    /** @var  EntityManagerInterface */
    private $em;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute([]);
    }

    public function testDependencyInjection()
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');

        $this->assertEquals(QueueBuilder::class, get_class($queueBuilder));
    }

    public function invalidQueueSizeDataProvider()
    {
        return [
            [0],
            [-1],
            [2.5],
            ['String'],
            [null],
        ];
    }

    /**
     * @dataProvider invalidQueueSizeDataProvider
     * @expectedException CronkdBundle\Exceptions\InvalidQueueIntervalException
     */
    public function testInvalidQueueSizes($expectedQueueSize)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld');
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource('Material');

        $queueBuilder->build($kingdom, $resource, $expectedQueueSize, 10);
    }

    public function validQueueSizeDataProvider()
    {
        return [
            [1],
            [2],
            [5],
            [10],
            [25],
            [100],
            [1000],
            [10000],
        ];
    }

    /**
     * @dataProvider validQueueSizeDataProvider
     */
    public function testValidQueueSizes($expectedQueueSize)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld');
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource('Material');

        $queues = $queueBuilder->build($kingdom, $resource, $expectedQueueSize, 10);

        $this->assertEquals($expectedQueueSize, count($queues));
    }

    private function createOrGetWorld($name, $tick = 1)
    {
        $world = $this->em->getRepository(World::class)->findOneBy(['name' => $name]);
        if (!$world) {
            $world = new World();
            $world->setName($name);
            $world->setTick($tick);
            $this->em->persist($world);
            $this->em->flush();
        }

        return $world;
    }

    private function createOrGetKingdom(World $world, $name)
    {
        $kingdom = $this->em->getRepository(Kingdom::class)->findOneBy([
            'world' => $world,
            'name' => $name,
        ]);
        if (!$kingdom) {
            $kingdom = new Kingdom();
            $kingdom->setName($name);
            $kingdom->setWorld($world);
            $this->em->persist($kingdom);
            $this->em->flush();
        }

        return $kingdom;
    }

    private function createOrGetResource($name)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneBy(['name' => $name]);
        if (!$resource) {
            $resource = new Resource();
            $resource->setName($name);
            $this->em->persist($resource);
            $this->em->flush();
        }

        return $resource;
    }
}