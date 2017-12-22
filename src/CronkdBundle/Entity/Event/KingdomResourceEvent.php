<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\KingdomResource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\NetWorthLogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class KingdomResourceEvent extends Event
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
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\KingdomResource")
     * @ORM\JoinColumn(name="kingdom_resource_id", referencedColumnName="id")
     */
    private $kingdomResource;

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return KingdomResourceEvent
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
     * Set kingdomResource
     *
     * @param KingdomResource $kingdomResource
     *
     * @return KingdomResourceEvent
     */
    public function setKingdomResource(KingdomResource $kingdomResource = null)
    {
        $this->kingdomResource = $kingdomResource;

        return $this;
    }

    /**
     * Get kingdomResource
     *
     * @return KingdomResource
     */
    public function getKingdomResource()
    {
        return $this->kingdomResource;
    }
}
