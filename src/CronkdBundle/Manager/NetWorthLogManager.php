<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\NetWorthLog;
use Doctrine\ORM\EntityManagerInterface;

class NetWorthLogManager
{
    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Kingdom $kingdom
     * @return NetWorthLog
     */
    public function logNetWorth(Kingdom $kingdom)
    {
        $netWorthLog = $this->findOrCreateNetWorthLog($kingdom);
        $netWorthLog->setNetWorth($kingdom->getNetWorth());
        $this->em->persist($netWorthLog);
        $this->em->flush();

        return $netWorthLog;
    }

    private function findOrCreateNetWorthLog(Kingdom $kingdom)
    {
        $currentTick = $kingdom->getWorld()->getTick();
        $netWorthLog = $this->em->getRepository(NetWorthLog::class)->findOneBy([
            'kingdom' => $kingdom,
            'tick'    => $currentTick,
        ]);

        if (!$netWorthLog) {
            $netWorthLog = new NetWorthLog();
            $netWorthLog->setKingdom($kingdom);
            $netWorthLog->setTick($currentTick);
            $this->em->persist($netWorthLog);
        }

        return $netWorthLog;
    }
}