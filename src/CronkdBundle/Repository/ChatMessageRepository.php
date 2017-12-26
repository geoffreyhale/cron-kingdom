<?php
namespace CronkdBundle\Repository;

use CronkdBundle\Entity\Kingdom;

class ChatMessageRepository extends \Doctrine\ORM\EntityRepository
{
    public function getUnreadMessageCount(Kingdom $kingdom)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('COUNT(m.id)');
        if ($kingdom->getLastReadChatMessage()) {
            $qb->where('m.createdAt > :kingdomLastReadMessageCreatedAt');
            $qb->setParameter('kingdomLastReadMessageCreatedAt', $kingdom->getLastReadChatMessage()->getCreatedAt()->format('Y-m-d H:i:s'));
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
