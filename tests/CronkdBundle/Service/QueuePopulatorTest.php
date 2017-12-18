<?php

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Service\QueuePopulator;
use Tests\Library\CronkdDatabaseAwareTestCase;

class QueuePopulatorTest extends CronkdDatabaseAwareTestCase
{
    /** @var  QueuePopulator */
    private $queuePopulator;

    public function setUp()
    {
        parent::setUp();
        $this->queuePopulator = $this->container->get('cronkd.queue_populator');
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(QueuePopulator::class, get_class($this->queuePopulator));
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
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');

        $this->queuePopulator->build($kingdom, $resource, $expectedQueueSize, 10);
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
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');

        $queues = $this->queuePopulator->build($kingdom, $resource, $expectedQueueSize, 10);

        $this->assertEquals($expectedQueueSize, count($queues));
    }

    public function bucketPlacementDataProvider()
    {
        return [
            [1, 1, [1]],
            [1, 10, [10]],
            [2, 1, [0,1]],
            [2, 7, [3,4]],
            [5, 29, [5,6,6,6,6]],
            [5, 25004, [5000,5001,5001,5001,5001]],
            [10, 75, [7,7,7,7,7,8,8,8,8,8]],
            [25, 1, [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1]],
            [1000, 100000, array_fill(0, 1000, 100)],
            [10, 10E12, array_fill(0, 10, 10E11)],
        ];
    }

    /**
     * @dataProvider bucketPlacementDataProvider
     */
    public function testBucketPlacement($queueSize, $quantity, array $expectedPlacement)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');

        $queues = $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);

        $this->assertEquals(count($expectedPlacement), count($queues));
        for ($i = 0; $i < count($queues); $i++) {
            $this->assertEquals($expectedPlacement[$i], $queues[$i]->getQuantity());
        }
    }

    public function queueStructureDataProvider()
    {
        return [
            [1, 10, 10],
            [100, 200, 30],
            [1000, 500, 3000],
        ];
    }

    /**
     * @dataProvider queueStructureDataProvider
     */
    public function testQueueStructure($startingTick, $queueSize, $quantity)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');
        $world    = $kingdom->getWorld();
        $world->setTick($startingTick);

        $queues = $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);

        for ($i = 0; $i < count($queues); $i++) {
            $this->assertEquals($kingdom->getWorld(), $queues[$i]->getKingdom()->getWorld());
            $this->assertEquals($kingdom, $queues[$i]->getKingdom());
            $this->assertEquals($resource, $queues[$i]->getResource());
            $this->assertEquals($startingTick+$i+1, $queues[$i]->getTick());
        }
    }
}