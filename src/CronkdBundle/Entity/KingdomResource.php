<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * KingdomResource
 *
 * @ORM\Table(name="kingdom_resource")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomResourceRepository")
 */
class KingdomResource
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
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="kingdoms", fetch="EAGER")
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
