<?php
namespace CronkdBundle\Repository\Event;

use CronkdBundle\Entity\World;
use Doctrine\ORM\EntityRepository;

class NetWorthEventRepository extends EntityRepository
{
    /**
     * @param World $world
     * @param int $minTick
     * @param int $maxTick
     * @return \stdClass
     */
    public function findByWorld(World $world, int $minTick, int $maxTick)
    {
        $qb = $this->createQueryBuilder('nwe');
        $qb->join('nwe.kingdom', 'k');
        $qb->where('k.world = :world');
        $qb->andWhere('nwe.tick BETWEEN :minTick AND :maxTick');
        $qb->setParameters([
            'world'   => $world,
            'minTick' => $minTick,
            'maxTick' => $maxTick,
        ]);
        $qb->orderBy('nwe.tick', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
