<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;

/**
 * QueueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QueueRepository extends \Doctrine\ORM\EntityRepository
{
    public function findCurrentByWorld(World $world)
    {
        $qb = $this->createQueryBuilder('q');
        $qb->join('q.kingdom', 'k');
        $qb->where('k.world = :world');
        $qb->andWhere('q.tick = :worldTick');
        $qb->setParameters([
            'world'     => $world,
            'worldTick' => $world->getTick(),
        ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param KingdomResource $kingdomResource
     * @return array
     */
    public function findCurrentQueues(KingdomResource $kingdomResource)
    {
        $qb = $this->createQueryBuilder('q');
        $qb->where('q.kingdom = :kingdom');
        $qb->andWhere('q.resource = :resource');
        $qb->andWhere('q.tick >= :tick');
        $qb->setParameters([
            'kingdom'  => $kingdomResource->getKingdom(),
            'resource' => $kingdomResource->getResource(),
            'tick'     => $kingdomResource->getKingdom()->getWorld()->getTick(),
        ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return int
     */
    public function findTotalQueued(Kingdom $kingdom, Resource $resource)
    {
        $qb = $this->createQueryBuilder('q');
        $qb->select('SUM(q.quantity) as qty');
        $qb->where('q.kingdom = :kingdom');
        $qb->andWhere('q.resource = :resource');
        $qb->andWhere('q.tick >= :currentTick');
        $qb->setParameters([
            'kingdom' => $kingdom,
            'resource' => $resource,
            'currentTick' => $kingdom->getWorld()->getTick(),
        ]);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
