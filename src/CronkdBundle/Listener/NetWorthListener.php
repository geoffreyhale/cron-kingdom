<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Event\ActionEvent;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ProbeEvent;
use CronkdBundle\Event\WorldTickEvent;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\NetWorthLogManager;
use Doctrine\ORM\EntityManagerInterface;

class NetWorthListener
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var KingdomManager */
    private $kingdomManager;
    /** @var NetWorthLogManager  */
    private $netWorthLogManager;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        NetWorthLogManager $netWorthLogManager
    )
    {
        $this->em                 = $em;
        $this->kingdomManager     = $kingdomManager;
        $this->netWorthLogManager = $netWorthLogManager;
    }

    public function onTick(WorldTickEvent $event)
    {
        /** @var Kingdom $kingdom */
        foreach ($event->world->getKingdoms() as $kingdom) {
            $this->kingdomManager->calculateNetWorth($kingdom);
            $this->netWorthLogManager->logNetWorth($kingdom);
        }
    }

    public function onCreateKingdom(CreateKingdomEvent $event)
    {
        if ($event->kingdom->getWorld()->getActive()) {
            $this->kingdomManager->calculateNetWorth($event->kingdom);
        }
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