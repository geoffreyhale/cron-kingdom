<?php
namespace CronkdBundle\Model;

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
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @param $quantity
     * @return AttackReport
     */
    public function addModifiedResource(Kingdom $kingdom, Resource $resource, $quantity)
    {
        $this->modifiedResources[] = $quantity . ' ' . $resource->getName();

        return $this;
    }

    /**
     * @return array
     */
    public function getModifiedResources()
    {
        return $this->modifiedResources;
    }
}