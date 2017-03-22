<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;

class LogRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByRecent(Kingdom $kingdom, int $limit)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->where('l.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);
        $qb->setMaxResults($limit);
        $qb->orderBy('l.tick', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
