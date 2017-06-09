<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Log;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ViewLogEvent;
use CronkdBundle\Exceptions\InvalidResourceException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;

class ResourceListener
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var  KingdomManager */
    private $kingdomManager;
    /** @var ResourceManager  */
    private $resourceManager;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager
    ) {
        $this->em              = $em;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @param CreateKingdomEvent $event
     * @throws InvalidResourceException
     */
    public function onCreateKingdom(CreateKingdomEvent $event)
    {
        if (!$event->kingdom->getWorld()->isActive()) {
            return;
        }

        $kingdom = $event->kingdom;

        $initialResources = $this->resourceManager->getKingdomStartingResources();
        foreach ($initialResources as $resourceName => $count) {
            $resource = $this->resourceManager->get($resourceName);
            if (!$resource) {
                throw new InvalidResourceException($resourceName);
            }

            $kingdomResource = $this->kingdomManager->findOrCreateResource($kingdom, $resource);
            $kingdomResource->setQuantity($count);
            $kingdom->addResource($kingdomResource);
        }

        $this->em->persist($kingdom);
        $this->em->flush();
    }
}