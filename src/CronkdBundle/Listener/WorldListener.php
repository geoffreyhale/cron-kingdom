<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Event\InitializeWorldEvent;
use CronkdBundle\Event\CreateKingdomEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorldListener
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em              = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onInitializeWorld(InitializeWorldEvent $event)
    {
        foreach ($event->world->getKingdoms() as $kingdom) {
            // Set initial resources, calculate initial net worth
            $event = new CreateKingdomEvent($kingdom);
            $this->eventDispatcher->dispatch('event.create_kingdom', $event);
        }
    }
}