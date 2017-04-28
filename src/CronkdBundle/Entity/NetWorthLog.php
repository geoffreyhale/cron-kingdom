<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="net_worth_log")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\NetWorthLogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class NetWorthLog extends BaseEntity
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
     * @var string
     *
     * @ORM\Column(name="net_worth", type="bigint")
     */
    private $netWorth;

    /**
     * @var int
     *
     * @ORM\Column(name="tick", type="bigint")
     */
    private $tick;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom", inversedBy="logs")
     */
    private $kingdom;

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
     * @return NetWorthLog
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
     * Set net worth
     *
     * @param int $netWorth
     *
     * @return NetWorthLog
     */
    public function setNetWorth($netWorth)
    {
        $this->netWorth = $netWorth;

        return $this;
    }

    /**
     * Get net worth
     *
     * @return int
     */
    public function getNetWorth()
    {
        return $this->netWorth;
    }

    /**
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return NetWorthLog
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
}
