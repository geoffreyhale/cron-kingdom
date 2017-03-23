<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Event\ActionEvent;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ProbeEvent;
use CronkdBundle\Event\WorldTickEvent;
use CronkdBundle\Service\KingdomManager;

class NetWorthListener
{
    /** @var KingdomManager */
    private $kingdomManager;

    public function __construct(KingdomManager $kingdomManager)
    {
        $this->kingdomManager = $kingdomManager;
    }

    public function onTick(WorldTickEvent $event)
    {
        foreach ($event->world->getKingdoms() as $kingdom) {
            $this->kingdomManager->calculateNetWorth($kingdom);
        }
    }

    public function onCreateKingdom(CreateKingdomEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
    }

    public function onAction(ActionEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
    }

    public function onProbe(ProbeEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
    }

    public function onAttack(AttackEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
        $this->kingdomManager->calculateNetWorth($event->target);
    }
}