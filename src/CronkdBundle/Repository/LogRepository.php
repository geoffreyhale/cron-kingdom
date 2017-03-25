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

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function findNotificationCount(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id) AS NotificationCount');
        $qb->where('l.important = 1');
        $qb->andWhere('l.readAt IS NULL');
        $qb->andWhere('l.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
