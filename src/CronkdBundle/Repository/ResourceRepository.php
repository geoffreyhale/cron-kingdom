<?php
namespace CronkdBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ResourceRepository extends EntityRepository
{
    /**
     * @param string $type
     * @return array
     */
    public function findResourcesByType(string $type)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->join('r.type', 'rt');
        $qb->where('rt.name = :name');
        $qb->setParameter('name', $type);

        return $qb->getQuery()->getResult();
    }
}
