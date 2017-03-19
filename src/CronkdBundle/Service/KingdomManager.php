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
        $housingResourcesCount = 0;
        $housingResources = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $housingResource,
        ]);
        if (null !== $housingResources) {
            $housingResourcesCount = $housingResources->getQuantity();
        }

        $totalCivilians = $activeCivilianResources + $inactiveCivilianResources;

        return $totalCivilians >= $housingResourcesCount;
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
        $housingResources = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $this->em->getRepository(Resource::class)->findOneBy([
                'name' => Resource::HOUSING,
            ])
        ]);

        $difference = floor(($housingResources->getQuantity() - $civilianResources->getQuantity()) / 10);

        $civilianResources->addQuantity($difference);
        $this->em->persist($civilianResources);
        $this->em->flush();

        return $difference;
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

        $kingdomResources = $this->em->getRepository(KingdomResource::class)->findByKingdom($kingdom);

        /** @var KingdomResource $kingdomResource */
        foreach ($kingdomResources as $kingdomResource) {
            $queues[] = [
                'kingdomResource' => $kingdomResource,
                'queues'          => $this->em->getRepository(Queue::class)->findCurrentQueues($kingdomResource),
            ];
        }

        return $queues;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param int $quantity
     * @return KingdomResource
     */
    public function modifyResources(Kingdom $kingdom, Resource $resource, int $quantity)
    {
        $this->logger->info('Modifying resource for Kingdom ' . $kingdom->getName() . '; Resource ' . $resource->getName() . '; Qty: ' . $quantity);

        $kingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $kingdom,
            'resource' => $resource,
        ]);
        if (!$kingdomResource) {
            $kingdomResource = new KingdomResource();
            $kingdomResource->setKingdom($kingdom);
            $kingdomResource->setResource($resource);
            $kingdomResource->setQuantity(0);
        }

        $kingdomResource->addQuantity($quantity);
        if (0 > $kingdomResource->getQuantity()) {
            $kingdomResource->setQuantity(0);
        }

        $this->em->persist($kingdomResource);
        $this->em->flush();

        return $kingdomResource;
    }
}