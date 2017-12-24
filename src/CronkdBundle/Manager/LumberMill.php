<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Event\AttackRewardEvent;
use CronkdBundle\Entity\Event\Event;
use CronkdBundle\Entity\Event\BirthEvent;
use CronkdBundle\Entity\Event\KingdomResourceEvent;
use CronkdBundle\Entity\Event\NetWorthEvent;
use CronkdBundle\Entity\Event\ProbeEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Notification\AttackNotification;
use CronkdBundle\Entity\Notification\ProbeNotification;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Event\AttackEvent;
use CronkdBundle\Model\AttackReport;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Serializer;

/**
 * Log Manager
 */
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
        $birthLog->setEventType(Event::TYPE_BIRTH);
        $birthLog->setTick($tick);
        $birthLog->setKingdom($kingdom);
        $birthLog->setQuantity($quantity);

        $kingdomResourceLog = new KingdomResourceEvent();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($quantity);
        $kingdomResourceLog->setEventType(Event::TYPE_BIRTH);
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
     * @param bool $fromProbe
     * @param bool $fromAttack
     * @param bool $reward
     */
    public function logQueueResource(Kingdom $kingdom, Resource $resource, $quantity, $fromProbe = false, $fromAttack = false, $reward = false)
    {
        if ($quantity == 0) {
            return;
        }

        $tick = $kingdom->getWorld()->getTick();
        $kingdomResource = $this->kingdomManager->findOrCreateKingdomResource($kingdom, $resource);

        $kingdomResourceLog = new KingdomResourceEvent();
        $kingdomResourceLog->setKingdom($kingdom);
        $kingdomResourceLog->setTick($tick);
        $kingdomResourceLog->setQuantity($quantity);
        $kingdomResourceLog->setEventType(Event::TYPE_QUEUE);
        $kingdomResourceLog->setKingdomResource($kingdomResource);
        $kingdomResourceLog->setIsFromProbe($fromProbe);
        $kingdomResourceLog->setIsFromAttack($fromAttack);
        $kingdomResourceLog->setIsReward($reward);
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

    /**
     * @param Kingdom $attacker
     * @param Kingdom $defender
     * @param AttackReport $report
     * @return AttackReport
     */
    public function logAttackResult(Kingdom $attacker, Kingdom $defender, AttackReport $report)
    {
        $tick = $attacker->getWorld()->getTick();

        $attackerEvent = new AttackResultEvent();
        $attackerEvent->setTick($tick);
        $attackerEvent->setEventType(Event::TYPE_ATTACK_RESULT);
        $attackerEvent->setAttacker($attacker);
        $attackerEvent->setDefender($defender);
        $attackerEvent->setSuccess($report->getResult());
        $attackerEvent->setReportData($this->serializer->serialize($report, 'json'));
        $attackerEvent->setKingdom($attacker);
        $this->em->persist($attackerEvent);

        $defenderEvent = clone $attackerEvent;
        $defenderEvent->setKingdom($defender);
        $defenderEvent->setReportData(null);
        $this->em->persist($defenderEvent);

        $defenderNotification = new AttackNotification();
        $defenderNotification->setKingdom($defender);
        $defenderNotification->setTick($tick);
        $defenderNotification->setAttacker($attacker);
        $defenderNotification->setSuccess($report->getResult());
        $this->em->persist($defenderNotification);

        $this->em->flush();

        $report->setAttackResultEvent($attackerEvent);

        return $report;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param $quantity int
     */
    public function logAttackReward(Kingdom $kingdom, Resource $resource, $quantity)
    {
        $this->logQueueResource($kingdom, $resource, $quantity, false, true, true);
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