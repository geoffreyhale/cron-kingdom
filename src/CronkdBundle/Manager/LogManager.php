<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log\BirthLog;
use CronkdBundle\Entity\Log\KingdomResourceLog;
use CronkdBundle\Entity\Log\Log;
use CronkdBundle\Entity\Log\NetWorthLog;
use CronkdBundle\Entity\Log\ProbeLog;
use CronkdBundle\Entity\Notification\ProbeNotification;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Serializer;

class LogManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var KingdomManager  */
    private $kingdomManager;
    /** @var Serializer  */
    private $serializer;

    public function __construct(EntityManagerInterface $em, KingdomManager $kingdomManager, Serializer $serializer)
    {
        $this->em             = $em;
        $this->kingdomManager = $kingdomManager;
        $this->serializer     = $serializer;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param int $quantity
     */
    public function logBirthEvent(Kingdom $kingdom, Resource $resource, int $quantity)
    {
        $tick = $kingdom->getWorld()->getTick();
        $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $resource);

        $birthLog = new BirthLog();
        $birthLog->setEventType(Log::TYPE_BIRTH);
        $birthLog->setTick($tick);
        $birthLog->setKingdom($kingdom);
        $birthLog->setQuantity($quantity);

        $kingdomResourceLog = new KingdomResourceLog();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($quantity);
        $kingdomResourceLog->setEventType(Log::TYPE_BIRTH);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $this->em->persist($kingdomResourceLog);

        $this->em->flush();
    }

    /**
     * @param Queue $queue
     */
    public function logDequeueResource(Queue $queue)
    {
        $kingdom = $queue->getKingdom();
        $tick = $kingdom->getWorld()->getTick();
        $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $queue->getResource());

        $kingdomResourceLog = new KingdomResourceLog();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($queue->getQuantity());
        $kingdomResourceLog->setEventType(Log::TYPE_DEQUEUE);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $this->em->persist($kingdomResourceLog);

        $this->em->flush();
    }

    public function logQueueResource(Kingdom $kingdom, Resource $resource, $quantity)
    {
        $tick = $kingdom->getWorld()->getTick();
        $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $resource);

        $kingdomResourceLog = new KingdomResourceLog();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($quantity);
        $kingdomResourceLog->setEventType(Log::TYPE_QUEUE);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $this->em->persist($kingdomResourceLog);

        $this->em->flush();
    }

    /**
     * @param Kingdom $prober
     * @param Kingdom $probee
     * @param ProbeReport $report
     */
    public function logProbeResult(Kingdom $prober, Kingdom $probee, ProbeReport $report)
    {
        $tick = $prober->getWorld()->getTick();

        $proberLog = new ProbeLog();
        $proberLog->setTick($tick);
        $proberLog->setEventType(Log::TYPE_PROBE);
        $proberLog->setProber($prober);
        $proberLog->setProbee($probee);
        $proberLog->setSuccess($report->getResult());
        $proberLog->setReportData($this->serializer->serialize($report->getData(), 'json'));
        $proberLog->setKingdom($prober);
        $this->em->persist($proberLog);

        $probeeLog = clone $proberLog;
        $probeeLog->setKingdom($probee);
        $probeeLog->setReportData(null);
        $this->em->persist($probeeLog);

        $probeeNotification = new ProbeNotification();
        $probeeNotification->setKingdom($probee);
        $probeeNotification->setTick($tick);
        $probeeNotification->setProber($prober);
        $probeeNotification->setSuccess($report->getResult());
        $this->em->persist($probeeNotification);

        $this->em->flush();
    }

    public function logAttackResult(Kingdom $attacker, Kingdom $defender)
    {

    }

    /**
     * @param Kingdom $kingdom
     * @return NetWorthLog
     */
    public function logNetWorth(Kingdom $kingdom)
    {
        $netWorthLog = new NetWorthLog();
        $netWorthLog->setKingdom($kingdom);
        $netWorthLog->setEventType(Log::TYPE_NET_WORTH);
        $netWorthLog->setNetWorth($kingdom->getNetWorth());
        $netWorthLog->setTick($kingdom->getWorld()->getTick());
        $this->em->persist($netWorthLog);
        $this->em->flush();

        return $netWorthLog;
    }
}