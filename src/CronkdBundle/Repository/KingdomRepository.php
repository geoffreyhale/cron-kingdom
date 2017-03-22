<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\User;
use CronkdBundle\Entity\World;

class KingdomRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns user's single kingdom from a world
     */
    public function findOneByUserWorld(User $user, World $world)
    {
        $qb = $this->createQueryBuilder('k');
        $qb->where('k.user = :user');
        $qb->setParameter('user', $user);
        if ($world) {
            $qb->andWhere('k.world = :world');
            $qb->setParameter('world', $world);
        }

        return $qb->getQuery()->getSingleResult();
    }

    public function userHasKingdom(User $user, World $world = null)
    {
        $qb = $this->createQueryBuilder('k');
        $qb->select('COUNT(k.id) AS HasKingdom');
        $qb->where('k.user = :user');
        $qb->setParameter('user', $user);
        if ($world) {
            $qb->andWhere('k.world = :world');
            $qb->setParameter('world', $world);
        }

        return $qb->getQuery()->getSingleScalarResult() ? true : false;
    }
}
