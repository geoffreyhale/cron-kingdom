<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class BirthEvent extends Event
{
    /**
     * @var string
     *
     * @ORM\Column(name="quantity", type="bigint")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Resource\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    private $resource;

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return BirthEvent
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set resource
     *
     * @param Resource $resource
     *
     * @return BirthEvent
     */
    public function setResource(Resource $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
