<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Event\ResetKingdomEvent;
use Doctrine\ORM\EntityManagerInterface;

class EloListener
{
    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    public function onAttack(AttackEvent $event)
    {
        $kd1 = $event->kingdom;
        $kd2 = $event->target;

        $r1 = $kd1->getElo();
        $r2 = $kd2->getElo();

        $tr1 = pow(10, $r1/400);
        $tr2 = pow(10, $r2/400);

        $e1 = $tr1/($tr1+$tr2);
        $e2 = $tr2/($tr1+$tr2);

        $s1 = $event->result ? 1 : 0;
        $s2 = $event->result ? 0 : 1;

        $k = 32;
        $elo1 = $r1 + $k * ($s1 - $e1);
        $elo2 = $r2 + $k * ($s2 - $e2);

        $kd1->setElo($elo1);
        $kd2->setElo($elo2);

        $this->em->persist($kd1);
        $this->em->persist($kd2);
        $this->em->flush();
    }

    public function onResetKingdom(ResetKingdomEvent $event)
    {
        $kingdom = $event->kingdom;
        $kingdom->setElo(Kingdom::DEFAULT_ELO);
        $this->em->persist($kingdom);
        $this->em->flush();
    }
}