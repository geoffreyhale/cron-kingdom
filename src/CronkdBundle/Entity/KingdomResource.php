<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * KingdomResource
 *
 * @ORM\Table(name="kingdom_resource")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomResourceRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class KingdomResource extends BaseEntity
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
     * @ORM\Column(name="quantity", type="bigint")
     * @Jms\Expose()
     */
    private $quantity;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom", inversedBy="resources")
     */
    private $kingdom;

    /**
     * @var Resource
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Resource\Resource", inversedBy="kingdoms", fetch="EAGER")
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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return KingdomResource
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
     * @return KingdomResource
     */
    public function addQuantity($quantity)
    {
        $this->setQuantity($this->getQuantity() + $quantity);

        return $this;
    }

    /**
     * @param $quantity
     * @return KingdomResource
     */
    public function removeQuantity($quantity)
    {
        $this->setQuantity($this->getQuantity() - $quantity);

        return $this;
    }

    /**
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return KingdomResource
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
     * @return KingdomResource
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
