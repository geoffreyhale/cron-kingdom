<?php

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Entity\World;
use CronkdBundle\Service\AttackingService;
use Tests\Library\CronkdDatabaseAwareTestCase;

class AttackingServiceTest extends CronkdDatabaseAwareTestCase
{
    /** @var  AttackingService */
    private $attackingService;
    /** @var  array */
    private $kingdoms;
    /** @var  array */
    private $resources;

    public function setUp()
    {
        parent::setUp();
        $this->attackingService = $this->container->get('cronkd.service.attacking');
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(AttackingService::class, get_class($this->attackingService));
    }

    public function attackDataProvider()
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
                    'Attacker' => 0,
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
                    'Attacker' => 0,
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
     * @dataProvider attackDataProvider
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
}