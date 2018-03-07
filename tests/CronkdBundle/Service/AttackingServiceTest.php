<?php

use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Policy\KingdomPolicy;
use CronkdBundle\Entity\Policy\KingdomPolicyInstance;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Service\AttackingService;
use Tests\Library\CronkdDatabaseAwareTestCase;

class AttackingServiceTest extends CronkdDatabaseAwareTestCase
{
    /** @var  AttackingService */
    private $attackingService;

    public function setUp()
    {
        parent::setUp();
        $this->attackingService = $this->container->get('cronkd.service.attacking');
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(AttackingService::class, get_class($this->attackingService));
    }

    public function attackResultDataProvider()
    {
        return [
            'no resources' => [
                'Hero',
                'Villain',
                [],
                [],
                [],
                false,
            ],
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender' => 0,
                ],
                true,
            ],
            'hero sends nothing to attack' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 0,
                ],
                [
                    'Defender' => 0,
                ],
                false,
            ],
            'hero loses, same number of resources' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender' => 1,
                ],
                false,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 10,
                ],
                [
                    'Attacker' => 10,
                ],
                [
                    'Defender' => 11,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider attackResultDataProvider
     */
    public function testAttackResult(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $intendedResult)
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);

        /** @var \CronkdBundle\Model\AttackReport $result */
        $result = $this->attackingService->attack($hero, $opponent, $attackingResources);

        $this->assertEquals($intendedResult, $result->getResult());
    }

    public function attackResultAttackPolicyDataProvider()
    {
        return [
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender' => 1,
                ],
                true,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 2,
                ],
                [
                    'Attacker' => 2,
                ],
                [
                    'Defender' => 5,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider attackResultAttackPolicyDataProvider
     */
    public function testAttackPolicies(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $intendedResult)
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);
        $policy = new KingdomPolicyInstance();
        $policy->setPolicy($this->em->getRepository(KingdomPolicy::class)->findOneByName('Attacker'));
        $policy->setKingdom($hero);
        $policy->setStartTick(1);
        $policy->setTickDuration(10);
        $opponent->addPolicy($policy);
        $this->em->persist($policy);
        $this->em->persist($hero);
        $this->em->flush();

        /** @var \CronkdBundle\Model\AttackReport $result */
        $result = $this->attackingService->attack($hero, $opponent, $attackingResources);

        $this->assertEquals($intendedResult, $result->getResult());
    }

    public function attackResultDefensePolicyDataProvider()
    {
        return [
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 3,
                ],
                [
                    'Attacker' => 3,
                ],
                [
                    'Attacker' => 0,
                ],
                true,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 10,
                ],
                [
                    'Attacker' => 10,
                ],
                [
                    'Defender' => 6,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider attackResultDefensePolicyDataProvider
     */
    public function testDefensePolicies(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $intendedResult)
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);
        $policy = new KingdomPolicyInstance();
        $policy->setPolicy($this->em->getRepository(KingdomPolicy::class)->findOneByName('Defender'));
        $policy->setKingdom($opponent);
        $policy->setStartTick(1);
        $policy->setTickDuration(10);
        $opponent->addPolicy($policy);
        $this->em->persist($policy);
        $this->em->persist($opponent);
        $this->em->flush();

        /** @var \CronkdBundle\Model\AttackReport $result */
        $result = $this->attackingService->attack($hero, $opponent, $attackingResources);

        $this->assertEquals($intendedResult, $result->getResult());
    }

    public function attackSpoilOfWarDataProvider()
    {
        return [
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender'   => 0,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 10,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 10,
                ],
                [
                    'Attacker' => 10,
                ],
                [
                    'Defender'   => 11,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 0,
            ],
        ];
    }

    /**
     * @dataProvider attackSpoilOfWarDataProvider
     */
    public function testSpoilOfWar(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $expectedOpponentLosses
    )
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);
        $originalSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;

        /** @var \CronkdBundle\Model\AttackReport $result */
        $this->attackingService->attack($hero, $opponent, $attackingResources);

        $newSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;
        $this->assertEquals($expectedOpponentLosses, $originalSpoilOfWarResourceCount-$newSpoilOfWarResourceCount);
    }

    public function attackSpoilOfWarAttackerPolicyDataProvider()
    {
        return [
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender'   => 0,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 20,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 10,
                ],
                [
                    'Attacker' => 10,
                ],
                [
                    'Defender'   => 11,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 0,
            ],
        ];
    }

    /**
     * @dataProvider attackSpoilOfWarAttackerPolicyDataProvider
     */
    public function testSpoilOfWarWithAttackerPolicies(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $expectedOpponentLosses
    )
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);
        $originalSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;
        $policy = new KingdomPolicyInstance();
        $policy->setPolicy($this->em->getRepository(KingdomPolicy::class)->findOneByName('Warmonger'));
        $policy->setKingdom($hero);
        $policy->setStartTick(1);
        $policy->setTickDuration(10);
        $opponent->addPolicy($policy);
        $this->em->persist($policy);
        $this->em->persist($hero);
        $this->em->flush();

        /** @var \CronkdBundle\Model\AttackReport $result */
        $this->attackingService->attack($hero, $opponent, $attackingResources);

        $newSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;
        $this->assertEquals($expectedOpponentLosses, $originalSpoilOfWarResourceCount-$newSpoilOfWarResourceCount);
    }

    public function attackSpoilOfWarDefenderPolicyDataProvider()
    {
        return [
            'hero wins' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 1,
                ],
                [
                    'Attacker' => 1,
                ],
                [
                    'Defender'   => 0,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 5,
            ],
            'hero loses' => [
                'Hero',
                'Villain',
                [
                    'Attacker' => 10,
                ],
                [
                    'Attacker' => 10,
                ],
                [
                    'Defender'   => 11,
                    'SpoilOfWar' => 100,
                ],
                'expectedOpponentLosses' => 0,
            ],
        ];
    }

    /**
     * @dataProvider attackSpoilOfWarDefenderPolicyDataProvider
     */
    public function testSpoilOfWarWithDefenderPolicies(
        $heroName,
        $opponentName,
        $heroResources,
        $attackingResources,
        $opponentResources,
        $expectedOpponentLosses
    )
    {
        $hero = $this->fillKingdomResources($this->fetchKingdom($heroName), $heroResources);
        $opponent = $this->fillKingdomResources($this->fetchKingdom($opponentName), $opponentResources);
        $originalSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;
        $policy = new KingdomPolicyInstance();
        $policy->setPolicy($this->em->getRepository(KingdomPolicy::class)->findOneByName('Safety'));
        $policy->setKingdom($opponent);
        $policy->setStartTick(1);
        $policy->setTickDuration(10);
        $opponent->addPolicy($policy);
        $this->em->persist($policy);
        $this->em->persist($opponent);
        $this->em->flush();

        /** @var \CronkdBundle\Model\AttackReport $result */
        $this->attackingService->attack($hero, $opponent, $attackingResources);

        $newSpoilOfWarResourceCount = $this->em->getRepository(KingdomResource::class)
            ->findOneBy([
                'resource' => $this->em->getRepository(Resource::class)->findOneByName('SpoilOfWar'),
                'kingdom' => $opponent,
            ])
            ->getQuantity()
        ;
        $this->assertEquals($expectedOpponentLosses, $originalSpoilOfWarResourceCount-$newSpoilOfWarResourceCount);
    }
}