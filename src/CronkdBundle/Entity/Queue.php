<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * Queue
 *
 * @ORM\Table(name="queue")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\QueueRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class Queue extends BaseEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="tick", type="bigint")
     *
     * @Jms\Expose()
     */
    private $tick;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="bigint")
     *
     * @Jms\Expose()
     */
    private $quantity;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom", inversedBy="queues", fetch="EAGER")
     *
     * @Jms\Expose()
     */
    private $kingdom;

    /**
     * @var Resource
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Resource\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     *
     * @Jms\Expose()
     */
    private $resource;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set tick
     *
     * @param integer $tick
     *
     * @return Queue
     */
    public function setTick($tick)
    {
        $this->tick = $tick;

        return $this;
    }

    /**
     * Get tick
     *
     * @return int
     */
    public function getTick()
    {
        return $this->tick;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Queue
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param $quantity
     * @return Queue
     */
    public function addQuantity($quantity)
    {
        $newQuantity = $this->getQuantity() + $quantity;

        return $this->setQuantity($newQuantity);
    }

    /**
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return Queue
     */
    public function setKingdom(Kingdom $kingdom = null)
    {
        $this->kingdom = $kingdom;

        return $this;
    }

    /**
     * Get kingdom
     *
     * @return Kingdom
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * Set resource
     *
     * @param Resource $resource
     *
     * @return Queue
     */
    public function setResource(Resource $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return \CronkdBundle\Entity\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
