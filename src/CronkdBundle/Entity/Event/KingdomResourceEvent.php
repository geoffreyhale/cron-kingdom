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
     * @var bool
     *
     * @ORM\Column(name="is_from_probe", type="boolean")
     */
    private $isFromProbe = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_from_attack", type="boolean")
     */
    private $isFromAttack = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_reward", type="boolean")
     */
    private $isReward = false;

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
     * Set isFromProbe
     *
     * @param boolean $isFromProbe
     *
     * @return KingdomResourceEvent
     */
    public function setIsFromProbe($isFromProbe)
    {
        $this->isFromProbe = $isFromProbe;

        return $this;
    }

    /**
     * Get isFromProbe
     *
     * @return boolean
     */
    public function getIsFromProbe()
    {
        return $this->isFromProbe;
    }

    /**
     * Set isFromAttack
     *
     * @param boolean $isFromAttack
     *
     * @return KingdomResourceEvent
     */
    public function setIsFromAttack($isFromAttack)
    {
        $this->isFromAttack = $isFromAttack;

        return $this;
    }

    /**
     * Get isFromAttack
     *
     * @return boolean
     */
    public function getIsFromAttack()
    {
        return $this->isFromAttack;
    }

    /**
     * Set isReward
     *
     * @param boolean $isReward
     *
     * @return KingdomResourceEvent
     */
    public function setIsReward($isReward)
    {
        $this->isReward = $isReward;

        return $this;
    }

    /**
     * Get isReward
     *
     * @return boolean
     */
    public function getIsReward()
    {
        return $this->isReward;
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
