<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\NetWorthLog;
use CronkdBundle\Entity\World;
use Doctrine\ORM\EntityRepository;

class NetWorthLogRepository extends EntityRepository
{
    /**
     * @param World $world
     * @param int $minTick
     * @param int $maxTick
     * @return \stdClass
     */
    public function findByWorld(World $world, int $minTick, int $maxTick)
    {
        $qb = $this->createQueryBuilder('nwl');
        $qb->join('nwl.kingdom', 'k');
        $qb->where('k.world = :world');
        $qb->andWhere('nwl.tick BETWEEN :minTick AND :maxTick');
        $qb->setParameters([
            'world'   => $world,
            'minTick' => $minTick,
            'maxTick' => $maxTick,
        ]);
        $qb->orderBy('nwl.tick', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
