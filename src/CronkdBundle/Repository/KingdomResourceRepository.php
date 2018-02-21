<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class KingdomResourceRepository extends EntityRepository
{
    /**
     * @param Kingdom $kingdom
     * @return array
     */
    public function findResourcesThatMayBeProbed(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('kr');
        $qb->join('kr.resource', 'r');
        $qb->where('r.canBeProbed = 1');
        $qb->andWhere('kr.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);

        $results = $qb->getQuery()->getResult();
        $keyedResults = [];
        foreach ($results as $result) {
            $keyedResults[$result->getResource()->getName()] = $result;
        }

        return $keyedResults;
    }

    /**
     * @param Kingdom $kingdom
     * @return array
     */
    public function findByKingdom(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('kr');
        $qb->join('kr.resource', 'r');
        $qb->where('kr.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);
        $qb->orderBy('r.name');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Kingdom $kingdom
     * @param array $resourceNames
     * @return array
     */
    public function findSpecificResources(Kingdom $kingdom, array $resourceNames)
    {
        $qb = $this->createQueryBuilder('kr');
        $qb->join('kr.resource', 'r');
        $qb->where($qb->expr()->in('r.name', $resourceNames));
        $qb->andWhere('kr.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Kingdom $kingdom
     * @param array $resources
     * @return int
     */
    public function findSumOfSpecificResources(Kingdom $kingdom, array $resources)
    {
        $qb = $this->createQueryBuilder('kr');
        $qb->select('SUM(kr.quantity) AS ResourceTotal');
        $qb->where($qb->expr()->in('kr.resource', $resources));
        $qb->andWhere('kr.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);
        $qb->groupBy('kr.kingdom');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }

    /**
     * @param Kingdom $kingdom
     * @return int
     */
    public function calculateLiquidity(Kingdom $kingdom)
    {
        $liquidity = 0;

        $qb = $this->createQueryBuilder('kr');
        $qb->join('kr.resource', 'r');
        $qb->where('kr.kingdom = :kingdom');
        $qb->setParameter('kingdom', $kingdom);

        $result = $qb->getQuery()->getResult();
        foreach ($result as $kingdomResource) {
            $liquidity += ($kingdomResource->getQuantity() * $kingdomResource->getResource()->getValue());
        }

        return $liquidity;
    }
}
