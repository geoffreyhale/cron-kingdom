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
class Log
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
}
