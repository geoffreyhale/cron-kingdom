<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource\Resource;
use JMS\Serializer\Annotation as Jms;

/**
 * @Jms\ExclusionPolicy("all")
 */
class AttackReport
{
    /**
     * @var Kingdom
     *
     * @Jms\Expose()
     */
    private $kingdom;

    /**
     * @var Kingdom
     *
     * @Jms\Expose()
     */
    private $targetKingdom;

    /**
     * @var bool
     *
     * @Jms\Expose()
     */
    private $result;

    /**
     * @var array
     *
     * @Jms\Expose()
     */
    private $queues = [];

    /**
     * @var array
     *
     * @Jms\Expose()
     */
    private $modifiedResources = [];

    /**
     * @var AttackResultEvent
     */
    private $attackResultEvent = null;

    public function __construct(Kingdom $kingdom, Kingdom $target, int $result)
    {
        $this->kingdom       = $kingdom;
        $this->targetKingdom = $target;
        $this->result        = 1 === $result ? true : false;
    }

    /**
     * @return bool
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param Resource $resource
     * @param array $queue
     * @return AttackReport
     */
    public function addQueue(Resource $resource, array $queue)
    {
        $this->queues[$resource->getName()] = $queue;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * @param Resource $resource
     * @param $quantity
     * @return AttackReport
     */
    public function addModifiedResource(Resource $resource, $quantity)
    {
        $this->modifiedResources[$resource->getName()] = $quantity;

        return $this;
    }

    /**
     * @return array
     */
    public function getModifiedResources()
    {
        return $this->modifiedResources;
    }

    /**
     * @return AttackResultEvent
     */
    public function getAttackResultEvent(): AttackResultEvent
    {
        return $this->attackResultEvent;
    }

    /**
     * @param AttackResultEvent $attackResultEvent
     * @return AttackReport
     */
    public function setAttackResultEvent(AttackResultEvent $attackResultEvent)
    {
        $this->attackResultEvent = $attackResultEvent;

        return $this;
    }
}