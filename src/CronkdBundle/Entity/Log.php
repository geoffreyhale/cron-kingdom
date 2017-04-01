<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\LogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class Log extends BaseEntity
{
    const TYPE_TICK   = 'tick';
    const TYPE_ACTION = 'action';
    const TYPE_PROBE  = 'hack';
    const TYPE_ATTACK = 'attack';

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
     * @ORM\Column(name="type", type="string", length=6)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="log", type="string", length=255)
     */
    private $log;

    /**
     * @var int
     *
     * @ORM\Column(name="tick", type="bigint")
     */
    private $tick;

    /**
     * @var bool
     *
     * @ORM\Column(name="important", type="boolean", options={"default" = 0})
     */
    private $important;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_at", type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom")
     * @ORM\JoinColumn(name="kingdom_id", referencedColumnName="id")
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
     * Set type
     *
     * @param string $type
     *
     * @return Log
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set tick
     *
     * @param integer $tick
     *
     * @return Log
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
     * Set log
     *
     * @param string $log
     *
     * @return Log
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return string
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set important
     *
     * @param boolean $important
     *
     * @return Log
     */
    public function setImportant($important)
    {
        $this->important = $important;

        return $this;
    }

    /**
     * Get important
     *
     * @return boolean
     */
    public function getImportant()
    {
        return $this->important;
    }

    /**
     * Set readAt
     *
     * @param \DateTime $readAt
     *
     * @return Log
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;

        return $this;
    }

    /**
     * Get readAt
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return Log
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
