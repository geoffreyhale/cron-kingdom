<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Event;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ResetKingdomEvent;
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
     */
    public function onCreateKingdom(CreateKingdomEvent $event)
    {
        if (!$event->kingdom->getWorld()->isActive()) {
            return;
        }

        $this->setStartingKingdomResources($event->kingdom);
    }

    /**
     * @param ResetKingdomEvent $event
     */
    public function onResetKingdom(ResetKingdomEvent $event)
    {
        if (!$event->kingdom->getWorld()->isActive()) {
            return;
        }

        $this->setStartingKingdomResources($event->kingdom);
    }

    private function setStartingKingdomResources(Kingdom $kingdom)
    {
        $world     = $kingdom->getWorld();
        $resources = $this->em->getRepository(Resource::class)->findByWorld($world);

        /** @var Resource $resource */
        foreach ($resources as $resource) {
            $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $resource);
            $kingdomResource->setQuantity($resource->getStartingAmount());
            $kingdom->addResource($kingdomResource);
        }

        $queues = $this->kingdomManager->getResourceQueues($kingdom);
        foreach ($queues as $queue) {
            foreach ($queue['queues'] as $queue) {
                $queue->setQuantity(0);
                $this->em->persist($queue);
            }
        }

        $this->em->persist($kingdom);
        $this->em->flush();
    }
}