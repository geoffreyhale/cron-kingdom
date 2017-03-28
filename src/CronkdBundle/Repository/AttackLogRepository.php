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
        $attackResults = $qb->getQuery()->getArrayResult();
        $qb2 = $this->createQueryBuilder('al');
        $qb2->select('al.success, COUNT(al.id) AS ResultCount');
        $qb2->where('al.defender = :kingdom');
        $qb2->setParameter('kingdom', $kingdom);
        $qb2->groupBy('al.success');
        $defendResults = $qb2->getQuery()->getArrayResult();

        $successes = 0;
        $failures = 0;
        foreach ($attackResults as $result) {
            if (true == $result['success']) {
                $successes += $result['ResultCount'];
            } else {
                $failures += $result['ResultCount'];
            }
        }
        foreach ($defendResults as $result) {
            if (true == $result['success']) {
                $failures += $result['ResultCount'];
            } else {
                $successes += $result['ResultCount'];
            }
        }

        return "$successes-$failures";
    }
}
