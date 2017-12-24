<?php
namespace CronkdBundle\Repository\Notification;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * @param Kingdom $kingdom
     * @param int $limit
     * @return array
     */
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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNotificationCount(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('COUNT(l.id) AS NotificationCount');
        $qb->where('l.readAt IS NULL');
        $qb->andWhere('l.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
