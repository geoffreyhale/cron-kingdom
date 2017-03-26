<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use Doctrine\ORM\EntityRepository;

class AttackLogRepository extends EntityRepository
{
    /**
     * @param Kingdom $kingdom
     * @return bool
     */
    public function hasAvailableAttack(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('al');
        $qb->select('COUNT(al.id) AS AttackCount');
        $qb->where('al.attacker = :kingdom');
        $qb->andWhere('al.tick = :tick');
        $qb->setParameters([
            'kingdom' => $kingdom,
            'tick'    => $kingdom->getWorld()->getTick(),
        ]);

        $result = $qb->getQuery()->getSingleScalarResult();

        return 0 == (int) $result ? true : false;
    }

    /**
     * @param Kingdom $kingdom
     * @return array
     */
    public function getWinLossRecord(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('al');
        $qb->select('al.success, COUNT(al.id) AS ResultCount');
        $qb->where('al.attacker = :kingdom');
        $qb->setParameter('kingdom', $kingdom);
        $qb->groupBy('al.success');

        $results = $qb->getQuery()->getArrayResult();
        $successes = 0;
        $failures = 0;
        foreach ($results as $result) {
            if (true == $result['success']) {
                $successes = $result['ResultCount'];
            } else {
                $failures = $result['ResultCount'];
            }
        }

        return "$successes-$failures";
    }
}
