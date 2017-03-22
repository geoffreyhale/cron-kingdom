<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;

class LogManager
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Kingdom $kingdom
     * @param string $type
     * @param string $message
     * @return Log
     */
    public function createLog(Kingdom $kingdom, string $type, string $message)
    {
        $log = new Log();
        $log->setKingdom($kingdom);
        $log->setTick($kingdom->getWorld()->getTick());
        $log->setType($type);
        $log->setLog($message);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }
}