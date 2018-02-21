<?php

use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\ResourceAction;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use CronkdBundle\Model\ResourceActionCostOverview;
use CronkdBundle\Service\ResourceActionService;
use Tests\Library\CronkdDatabaseAwareTestCase;

class ResourceActionServiceTest extends CronkdDatabaseAwareTestCase
{
    /** @var  ResourceActionService */
    private $resourceActionService;

    public function setUp()
    {
        parent::setUp();
        $this->resourceActionService = $this->container->get('cronkd.service.resource_action');
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(ResourceActionService::class, get_class($this->resourceActionService));
    }

    public function costOverviewGeneratorDataProvider()
    {
        return [
            'static' => [
                [
                    'name' => 'Soldier',
                    'output' => 1,
                    'queue_size' => 8,
                ],
                [
                    [
                        'name' => 'Civilian',
                        'strategy' => 'static',
                        'input_quantity' => 1,
                        'requeue' => false,
                    ],
                ],
                [
                    [
                        'name' => 'Civilian',
                        'quantity' => 50,
                    ],
                    [
                        'name' => 'Soldier',
                        'quantity' => 0,
                    ],
                ],
                [
                    'maxQuantity' => 50,
                    'maxQuantityInputs' => [
                        'Civilian' => [
                            'maxQuantity' => 50,
                            'cost' => 1,
                            'maxCost' => 50,
                            'strategy' => 'static',
                        ],
                    ]
                ]
            ],

            'less than 0 quantity check' => [
                [
                    'name' => 'House',
                    'output' => 1,
                    'queue_size' => 8,
                ],
                [
                    [
                        'name' => 'Land',
                        'strategy' => 'requirement',
                        'input_quantity' => 0,
                        'requeue' => false,
                    ],
                ],
                [
                    [
                        'name' => 'House',
                        'quantity' => 60,
                    ],
                    [
                        'name' => 'Land',
                        'quantity' => 50,
                    ],
                ],
                [
                    'maxQuantity' => 0,
                    'maxQuantityInputs' => [
                        'Land' => [
                            'maxQuantity' => -10,
                            'cost' => 60,
                            'maxCost' => 60,
                            'strategy' => 'requirement',
                        ],
                    ]
                ]
            ],

            'additive' => [
                [
                    'name' => 'House',
                    'output' => 1,
                    'queue_size' => 8,
                ],
                [
                    [
                        'name' => 'Civilian',
                        'strategy' => 'additive',
                        'input_quantity' => 0,
                        'requeue' => true,
                    ],
                ],
                [
                    [
                        'name' => 'House',
                        'quantity' => 50,
                    ],
                    [
                        'name' => 'Civilian',
                        'quantity' => 320,
                    ],
                ],
                [
                    'maxQuantity' => 6,
                    'maxQuantityInputs' => [
                        'Civilian' => [
                            'maxQuantity' => 6,
                            'cost' => 50,
                            'maxCost' => 315,
                            'strategy' => 'additive',
                        ],
                    ]
                ]
            ],

            'exponential' => [
                [
                    'name' => 'Land',
                    'output' => 1,
                    'queue_size' => 8,
                ],
                [
                    [
                        'name' => 'Civilian',
                        'strategy' => 'exponential',
                        'input_quantity' => 0,
                        'requeue' => false,
                    ],
                ],
                [
                    [
                        'name' => 'Land',
                        'quantity' => 50,
                    ],
                    [
                        'name' => 'Civilian',
                        'quantity' => 50,
                    ],
                ],
                [
                    'maxQuantity' => 3,
                    'maxQuantityInputs' => [
                        'Civilian' => [
                            'maxQuantity' => 3,
                            'cost' => 15,
                            'maxCost' => 45,
                            'strategy' => 'exponential',
                        ],
                    ]
                ]
            ],

            'requirement' => [
                [
                    'name' => 'House',
                    'output' => 1,
                    'queue_size' => 8,
                ],
                [
                    [
                        'name' => 'Civilian',
                        'strategy' => 'static',
                        'input_quantity' => 1,
                        'requeue' => true,
                    ],
                    [
                        'name' => 'Land',
                        'strategy' => 'requirement',
                        'input_quantity' => 0,
                        'requeue' => false,
                    ],
                ],
                [
                    [
                        'name' => 'House',
                        'quantity' => 0,
                    ],
                    [
                        'name' => 'Land',
                        'quantity' => 50,
                    ],
                    [
                        'name' => 'Civilian',
                        'quantity' => 100,
                    ],
                ],
                [
                    'maxQuantity' => 50,
                    'maxQuantityInputs' => [
                        'Civilian' => [
                            'maxQuantity' => 100,
                            'cost' => 1,
                            'maxCost' => 100,
                            'strategy' => 'static',
                        ],
                        'Land' => [
                            'maxQuantity' => 50,
                            'cost' => 0,
                            'maxCost' => 0,
                            'strategy' => 'requirement',
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider costOverviewGeneratorDataProvider
     */
    public function testCostOverviewGeneration(array $actionDetails, array $inputResources, array $kingdomResources, array $expectedOutput)
    {
        $action = new ResourceAction();
        $action->setResource($this->fetchResource($actionDetails['name']));
        $action->setVerb('Test');
        $action->setOutputQuantity($actionDetails['output']);
        $action->setQueueSize($actionDetails['queue_size']);
        $action->setDescription('Test');
        foreach ($inputResources as $inputDetails) {
            $input = new ResourceActionInput();
            $input->setResource($this->fetchResource($inputDetails['name']));
            $input->setInputStrategy($inputDetails['strategy']);
            $input->setInputQuantity($inputDetails['input_quantity']);
            $input->setRequeue($inputDetails['requeue']);
            $input->setResourceAction($action);
            $action->addInput($input);
            $this->em->persist($input);
        }
        $this->em->persist($action);
        $kingdom = $this->fetchKingdom('Hero');
        foreach ($kingdomResources as $kingdomResourceDetails) {
            $kingdomResource = new KingdomResource();
            $kingdomResource->setKingdom($kingdom);
            $kingdomResource->setResource($this->fetchResource($kingdomResourceDetails['name']));
            $kingdomResource->setQuantity($kingdomResourceDetails['quantity']);
            $kingdom->addResource($kingdomResource);
            $this->em->persist($kingdomResource);
        }
        $this->em->persist($kingdom);
        $this->em->flush();

        $overview = $this->resourceActionService->getCostsForResource($kingdom, $action);

        $this->assertEquals(ResourceActionCostOverview::class, get_class($overview));
        $this->assertEquals($expectedOutput['maxQuantity'], $overview->getMaxQuantityToProduce());
        $this->assertCount(count($expectedOutput['maxQuantityInputs']), $overview->getOverview());
        foreach ($expectedOutput['maxQuantityInputs'] as $resourceName => $expectedInputData) {
            $this->assertEquals($expectedInputData['maxQuantity'], $overview->getOverview()[$resourceName]['maxQuantity']);
            $this->assertEquals($expectedInputData['cost'], $overview->getOverview()[$resourceName]['cost']);
            $this->assertEquals($expectedInputData['maxCost'], $overview->getOverview()[$resourceName]['maxCost']);
            $this->assertEquals($expectedInputData['strategy'], $overview->getOverview()[$resourceName]['strategy']);
        }
    }
}