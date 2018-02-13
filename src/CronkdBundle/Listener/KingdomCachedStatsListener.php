<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Event\ActionEvent;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Event\CreateKingdomEvent;
use CronkdBundle\Event\ResetKingdomEvent;
use CronkdBundle\Event\WorldTickEvent;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\LumberMill;
use CronkdBundle\Manager\NetWorthLogManager;
use Doctrine\ORM\EntityManagerInterface;

class KingdomCachedStatsListener
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var KingdomManager */
    private $kingdomManager;
    /** @var LumberMill  */
    private $logManager;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        LumberMill $logManager
    )
    {
        $this->em             = $em;
        $this->kingdomManager = $kingdomManager;
        $this->logManager     = $logManager;
    }

    public function onTick(WorldTickEvent $event)
    {
        /** @var Kingdom $kingdom */
        foreach ($event->world->getKingdoms() as $kingdom) {
            $this->kingdomManager->calculateNetWorth($kingdom);
            $this->kingdomManager->calculateAttackAndDefense($kingdom);
            $this->logManager->logNetWorth($kingdom);
        }
    }

    public function onCreateKingdom(CreateKingdomEvent $event)
    {
        if ($event->kingdom->getWorld()->isActive()) {
            $this->kingdomManager->calculateNetWorth($event->kingdom);
            $this->kingdomManager->calculateAttackAndDefense($event->kingdom);
        }
    }

    public function onResetKingdom(ResetKingdomEvent $event)
    {
        if ($event->kingdom->getWorld()->isActive()) {
            $this->kingdomManager->calculateNetWorth($event->kingdom);
            $this->kingdomManager->calculateAttackAndDefense($event->kingdom);
        }
    }

    public function onAction(ActionEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
        $this->kingdomManager->calculateAttackAndDefense($event->kingdom);
    }

    public function onAttack(AttackEvent $event)
    {
        $this->kingdomManager->calculateNetWorth($event->kingdom);
        $this->kingdomManager->calculateNetWorth($event->target);
        $this->kingdomManager->calculateAttackAndDefense($event->kingdom);

    }
}