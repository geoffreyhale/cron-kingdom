<?php
namespace CronkdBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class WorldRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findUpcomingWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.startTime > :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return World|null
     */
    public function findActiveWorld()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.endTime > :now');
        $qb->andWhere('w.startTime < :now');
        $qb->setParameter('now', new \DateTime());
        $qb->setMaxResults(1);

        $world = null;
        try {
            $world = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            // No result is actually okay.
            ;
        }

        return $world;
    }

    /**
     * @return array
     */
    public function findActiveWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.endTime > :now');
        $qb->andWhere('w.startTime < :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findInactiveWorlds()
    {
        $qb = $this->createQueryBuilder('w');
        $qb->where('w.startTime < :now');
        $qb->setParameter('now', new \DateTime());

        return $qb->getQuery()->getResult();
    }
}
