<?php

use CronkdBundle\Command\TickCommand;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Policy\Policy;
use CronkdBundle\Entity\Policy\PolicyInstance;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Service\QueuePopulator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Library\CronkdDatabaseAwareTestCase;

class TickServiceTest extends CronkdDatabaseAwareTestCase
{
    /** @var  QueuePopulator */
    private $queuePopulator;

    public function setUp()
    {
        parent::setUp();
        $this->queuePopulator = $this->container->get('cronkd.queue_populator');
    }

    public function queueDataProvider()
    {
        return [
            [1, 10],
            [2, 200],
            [8, 500],
            [16, 1600],
        ];
    }

    /**
     * @dataProvider queueDataProvider
     */
    public function testTickPaysOffQueues($queueSize, $quantity)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');
        $world    = $kingdom->getWorld();
        $this->fillKingdomResources($kingdom, ['Material' => 0]);

        $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);
        $world->setTick(0); // Set to previous tick
        $this->em->persist($world);
        $this->em->flush();
        $application = new Application(self::$kernel);
        $application->add(new TickCommand());
        $command = $application->find('cronkd:tick');
        $commandTester = new CommandTester($command);
        for ($i = 0; $i <= $queueSize; $i++) {
            $commandTester->execute(['command' => $command->getName()]);
        }

        $resourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $resource,
                'kingdom' => $kingdom,
            ])
            ->getQuantity()
        ;
        $this->assertEquals($quantity, $resourceCount);
    }

    public function queueWithOutputMultiplierDataProvider()
    {
        return [
            [1, 10, 100, 10],
            [2, 200, 50, 100],
            [8, 500, 0, 0],
            [8, 1600, -50, 0],
        ];
    }

    /**
     * @dataProvider queueWithOutputMultiplierDataProvider
     */
    public function testTickPaysOffWithOutputMultiplierQueues($queueSize, $quantity, $multiplier, $expectedOutput)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneByName('Material');
        $kingdom  = $this->em->getRepository(Kingdom::class)->findOneByName('Hero');
        $world    = $kingdom->getWorld();
        $this->fillKingdomResources($kingdom, ['Material' => 0]);
        $policy = new Policy();
        $policy->setName('OutputMultiplier');
        $policy->setOutputMultiplier($multiplier);
        $policyInstance = new PolicyInstance();
        $policyInstance->setPolicy($policy);
        $policyInstance->setKingdom($kingdom);
        $policyInstance->setStartTick(1);
        $policyInstance->setTickDuration(10);
        $kingdom->addPolicy($policyInstance);
        $this->em->persist($policyInstance);
        $this->em->persist($kingdom);
        $this->em->persist($policy);
        $this->em->flush();

        $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);
        $world->setTick(0); // Set to previous tick
        $this->em->persist($world);
        $this->em->flush();
        $application = new Application(self::$kernel);
        $application->add(new TickCommand());
        $command = $application->find('cronkd:tick');
        $commandTester = new CommandTester($command);
        for ($i = 0; $i <= $queueSize; $i++) {
            $commandTester->execute(['command' => $command->getName()]);
        }

        $resourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $resource,
                'kingdom' => $kingdom,
            ])
            ->getQuantity()
        ;
        $this->assertEquals($expectedOutput, $resourceCount);
    }
}