<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\EntityRepository;

class WorldPolicyInstanceRepository extends EntityRepository
{
    /**
     * @param Kingdom $kingdom
     * @return array
     */
    public function findActivePolicies(Kingdom $kingdom)
    {
        $currentTick = $kingdom->getWorld()->getTick();
        $qb = $this->createQueryBuilder('wpi');
        $qb->where('wpi.kingdom = :kingdom');
        $qb->andWhere('wpi.startTick <= :currentTick');
        $qb->setParameters([
            'kingdom'     => $kingdom,
            'currentTick' => $currentTick,
        ]);

        $current = [];
        $results = $qb->getQuery()->getResult();
        foreach ($results as $policy) {
            if ($currentTick < ($policy->getStartTick() + $policy->getTickDuration())) {
                $current[] = $policy;
            }
        }

        return $current;
    }
}
