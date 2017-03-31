<?php
namespace CronkdBundle\Repository;

use Doctrine\ORM\EntityRepository;

class WorldRepository extends EntityRepository
{
    public function findUpcomingWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.active = 0');
        $qb->andWhere('w.startTime > :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function findActiveWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.active = 1');
        $qb->andWhere('w.endTime > :now');
        $qb->andWhere('w.startTime < :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function findInactiveWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.active = 0');
        $qb->andWhere('w.startTime < :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }
}
