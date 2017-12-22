<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Event\Event;
use CronkdBundle\Entity\Event\BirthEvent;
use CronkdBundle\Entity\Event\KingdomResourceEvent;
use CronkdBundle\Entity\Event\NetWorthEvent;
use CronkdBundle\Entity\Event\ProbeEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Notification\ProbeNotification;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Serializer;

class LumberMill
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

        $birthLog = new BirthEvent();
        $birthLog->setEventType(Log::TYPE_BIRTH);
        $birthLog->setTick($tick);
        $birthLog->setKingdom($kingdom);
        $birthLog->setQuantity($quantity);

        $kingdomResourceLog = new KingdomResourceEvent();
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

        $kingdomResourceLog = new KingdomResourceEvent();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($queue->getQuantity());
        $kingdomResourceLog->setEventType(Event::TYPE_DEQUEUE);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $this->em->persist($kingdomResourceLog);

        $this->em->flush();
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param $quantity
     */
    public function logQueueResource(Kingdom $kingdom, Resource $resource, $quantity)
    {
        $tick = $kingdom->getWorld()->getTick();
        $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $resource);

        $kingdomResourceLog = new KingdomResourceEvent();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($quantity);
        $kingdomResourceLog->setEventType(Event::TYPE_QUEUE);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $this->em->persist($kingdomResourceLog);

        $this->em->flush();
    }

    /**
     * @param Kingdom $prober
     * @param Kingdom $probee
     * @param ProbeReport $report
     * @return ProbeEvent
     */
    public function logProbeResult(Kingdom $prober, Kingdom $probee, ProbeReport $report)
    {
        $tick = $prober->getWorld()->getTick();

        $proberEvent = new ProbeEvent();
        $proberEvent->setTick($tick);
        $proberEvent->setEventType(Event::TYPE_PROBE);
        $proberEvent->setProber($prober);
        $proberEvent->setProbee($probee);
        $proberEvent->setSuccess($report->getResult());
        $proberEvent->setReportData($this->serializer->serialize($report->getData(), 'json'));
        $proberEvent->setKingdom($prober);
        $this->em->persist($proberEvent);

        $probeeEvent = clone $proberEvent;
        $probeeEvent->setKingdom($probee);
        $probeeEvent->setReportData(null);
        $this->em->persist($probeeEvent);

        $probeeNotification = new ProbeNotification();
        $probeeNotification->setKingdom($probee);
        $probeeNotification->setTick($tick);
        $probeeNotification->setProber($prober);
        $probeeNotification->setSuccess($report->getResult());
        $this->em->persist($probeeNotification);

        $this->em->flush();

        return $proberEvent;
    }

    public function logAttackResult(Kingdom $attacker, Kingdom $defender)
    {

    }

    /**
     * @param Kingdom $kingdom
     * @return NetWorthEvent
     */
    public function logNetWorth(Kingdom $kingdom)
    {
        $netWorthLog = new NetWorthEvent();
        $netWorthLog->setKingdom($kingdom);
        $netWorthLog->setEventType(Event::TYPE_NET_WORTH);
        $netWorthLog->setNetWorth($kingdom->getNetWorth());
        $netWorthLog->setTick($kingdom->getWorld()->getTick());
        $this->em->persist($netWorthLog);
        $this->em->flush();

        return $netWorthLog;
    }
}