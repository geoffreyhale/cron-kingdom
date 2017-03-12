<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class KingdomManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var NullLogger  */
    private $logger;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     * @return KingdomManager
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Kingdom $kingdom
     * @return bool
     */
    public function isAtMaxPopulation(Kingdom $kingdom)
    {
        $civilianResource = $this->em->getRepository(Resource::class)->findOneBy([
            'name' => Resource::CIVILIAN,
        ]);
        $housingResource = $this->em->getRepository(Resource::class)->findOneBy([
            'name' => Resource::HOUSING,
        ]);

        $activeCivilianResources = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $civilianResource,
        ])->getQuantity();
        $inactiveCivilianResources = $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $civilianResource);
        $housingResources = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $housingResource,
        ])->getQuantity();

        $totalCivilians = $activeCivilianResources + $inactiveCivilianResources;
        $this->logger->info($activeCivilianResources . ' + ' . $inactiveCivilianResources . ' ?= ' . $housingResources);

        return $totalCivilians >= $housingResources;
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function incrementPopulation(Kingdom $kingdom)
    {
        /** @var KingdomResource $civilianResources */
        $civilianResources = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $this->em->getRepository(Resource::class)->findOneBy([
                'name' => Resource::CIVILIAN,
            ])
        ]);

        $civilianResources->addQuantity(1);
        $this->em->persist($civilianResources);
        $this->em->flush();

        return $civilianResources->getQuantity();
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function calculateNetWorth(Kingdom $kingdom)
    {
        $netWorth = 0;

        /** @var KingdomResource $resource */
        foreach ($kingdom->getResources() as $resource) {
            $netWorth += $resource->getQuantity() * $resource->getResource()->getValue();
        }

        $kingdom->setNetWorth($netWorth);
        $this->em->persist($kingdom);
        $this->em->flush();

        return $netWorth;
    }

    /**
     * @param Kingdom $kingdom
     * @return Queue[]
     */
    public function getResourceQueues(Kingdom $kingdom)
    {
        $queues = [];

        /** @var KingdomResource $kingdomResource */
        foreach ($kingdom->getResources() as $kingdomResource) {
            $queues[] = [
                'kingdomResource' => $kingdomResource,
                'queues'          => $this->em->getRepository(Queue::class)->findCurrentQueues($kingdomResource),
            ];
        }

        return $queues;
    }
}