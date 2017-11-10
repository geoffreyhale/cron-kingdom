<?php

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Service\AttackingService;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AttackingServiceTest extends KernelTestCase
{
    /** @var  ContainerInterface */
    private $container;
    /** @var  EntityManagerInterface */
    private $em;
    /** @var  AttackingService */
    private $attackingService;
    /** @var  array */
    private $kingdoms;
    /** @var  array */
    private $resources;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');
        $this->attackingService = $this->container->get('cronkd.service.attacking');

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute([]);
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(AttackingService::class, get_class($this->attackingService));
    }

    public function attackDataProvider()
    {
        return [
            'no resources' => [
                [],
                [],
                [],
                false,
            ],
            'hero loses' => [
                [
                    (new KingdomResource())
                        ->setResource($this->createResource('Army'))
                        ->setKingdom($this->createKingdom('Hero'))
                        ->setQuantity(1),
                ],
                [
                    (new KingdomResource())
                        ->setResource($this->createResource('Army'))
                        ->setKingdom($this->createKingdom('Hero'))
                        ->setQuantity(0),
                ],
                [
                    (new KingdomResource())
                        ->setResource($this->createResource('Army'))
                        ->setKingdom($this->createKingdom('Opponent'))
                        ->setQuantity(1)
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider attackDataProvider
     */
    public function testAttackResult($heroResources, $attackingResources, $opponentResources, $intendedResult)
    {
        $hero = $this->createKingdom('Hero', $heroResources);
        $opponent = $this->createKingdom('Opponent', $opponentResources);
        foreach ($heroResources as $kingdomResource) {
            $hero->addResource($kingdomResource);
        }
        foreach ($opponentResources as $kingdomResource) {
            $opponent->addResource($kingdomResource);
        }
        foreach ($attackingResources as $kingdomResource) {
            $attackingResources[$kingdomResource->getResource()->getName()] = $kingdomResource->getQuantity();
        }
        $this->em->flush();

        $result = $this->attackingService->attack($hero, $opponent, $attackingResources);

        $this->assertEquals($intendedResult, $result);
    }

    /**
     * @param string $name
     * @param Resource[] $resources
     * @return Kingdom
     */
    private function createKingdom($name, array $resources)
    {
        if (isset($this->kingdoms[$name])) {
            return $this->kingdoms[$name];
        }

        $kingdom = new Kingdom();
        $kingdom->setName($name);
        $this->em->persist($kingdom);
        $this->em->flush();

        $this->kingdoms[$name] = $kingdom;

        return $kingdom;
    }

    private function createResource($name)
    {
        if (isset($this->resources[$name])) {
            return $this->resources[$name];
        }

        $resourceType = new ResourceType();
        $resourceType->setName('Resource');
        $this->em->persist($resourceType);

        $resource = new Resource();
        $resource->setType($resourceType);
        switch ($name) {
            case 'Army':
                $resource->setName($name);
                $resource->setAttack(1);
                $resource->setSpoilOfWar(false);
                $resource->setStartingAmount(0);
                break;
        }

        $this->em->persist($resource);
        $this->em->flush();

        $this->resources[$name] = $resource;

        return $resource;
    }
}