<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\EntityRepository;

class KingdomPolicyRepository extends EntityRepository
{
    /**
     * @param Kingdom $kingdom
     * @return bool
     */
    public function kingdomHasActivePolicy(Kingdom $kingdom, string $policyName = null)
    {
        $qb = $this->createQueryBuilder('kp');
        $qb->select('COUNT(kp.id) AS KingdomPolicyCount');
        $qb->where('kp.kingdom = :kingdom');
        $qb->andWhere('kp.startTime < :now1');
        $qb->andWhere('kp.endTime > :now2');
        $parameters = [
            'kingdom' => $kingdom,
            'now1'    => new \DateTime(),
            'now2'    => new \DateTime(),
        ];
        if (null !== $policyName) {
            $qb->join('kp.policy', 'p');
            $qb->andWhere('p.name = :policy');
            $parameters['policy'] = $policyName;
        }
        $qb->setParameters($parameters);

        $result = $qb->getQuery()->getSingleScalarResult();
        if (0 == $result) {
            return false;
        }

        return true;
    }

    /**
     * @param Kingdom $kingdom
     * @return array
     */
    public function findCurrentPolicy(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('kp');
        $qb->where('kp.kingdom = :kingdom');
        $qb->andWhere('kp.startTime < :now1');
        $qb->andWhere('kp.endTime > :now2');
        $qb->setParameters([
            'kingdom' => $kingdom,
            'now1'    => new \DateTime(),
            'now2'    => new \DateTime(),
        ]);

        $policy = $qb->getQuery()->getResult();
        if (count($policy)) {
            return $policy[0]->getPolicy()->getName();
        }
        return 'None';
    }
}
