<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * World
 *
 * @ORM\Table(name="world")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\WorldRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class World extends BaseEntity
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
     * @ORM\Column(name="tick", type="bigint", options={"default": 1})
     */
    private $tick;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * Tick interval in minutes.
     *
     * @var int
     *
     * @ORM\Column(name="tick_interval", type="integer")
     * @Assert\Range(min=1, minMessage="Interval must be greater than zero.")
     */
    private $tickInterval;

    /**
     * Countdown till next tick.
     *
     * @var int
     *
     * @ORM\Column(name="minutes_since_last_tick", type="integer")
     */
    private $minutesSinceLastTick;

    /**
     * @var Kingdom[]
     *
     * @ORM\OneToMany(targetEntity="Kingdom", mappedBy="world")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $kingdoms;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms = new ArrayCollection();
    }

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
     * @return World
     */
    public function setTick($tick)
    {
        $this->tick = $tick;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setDefaultTick()
    {
        if (null === $this->tick) {
            $this->setTick(0);
        }
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
     * @return World
     */
    protected function addTick()
    {
        $tick = $this->getTick();
        $this->setTick(++$tick);

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return World
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return World
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return World
     */
    public function setDefaultActive()
    {
        if (null === $this->active) {
            return $this->setActive(false);
        }

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return World
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return World
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set tickInterval
     *
     * @param integer $tickInterval
     *
     * @return World
     */
    public function setTickInterval($tickInterval)
    {
        $this->tickInterval = $tickInterval;

        return $this;
    }

    /**
     * Get tickInterval
     *
     * @return integer
     */
    public function getTickInterval()
    {
        return $this->tickInterval;
    }

    /**
     * Set minutesSinceLastTick
     *
     * @param integer $minutesSinceLastTick
     *
     * @return World
     */
    public function setMinutesSinceLastTick($minutesSinceLastTick)
    {
        $this->minutesSinceLastTick = $minutesSinceLastTick;

        return $this;
    }

    /**
     * Get minutesSinceLastTick
     *
     * @return integer
     */
    public function getMinutesSinceLastTick()
    {
        return $this->minutesSinceLastTick;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return World
     */
    public function setDefaultMinutesSinceLastTick()
    {
        if (null === $this->active) {
            return $this->setMinutesSinceLastTick(0);
        }

        return $this;
    }

    /**
     * @return World
     */
    protected function addMinuteSinceLastTick()
    {
        $min = $this->getMinutesSinceLastTick();
        $this->setMinutesSinceLastTick(++$min);

        return $this;
    }

    /**
     * Add kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return World
     */
    public function addKingdom(Kingdom $kingdom)
    {
        $this->kingdoms[] = $kingdom;

        return $this;
    }

    /**
     * Remove kingdom
     *
     * @param Kingdom $kingdom
     */
    public function removeKingdom(Kingdom $kingdom)
    {
        $this->kingdoms->removeElement($kingdom);
    }

    /**
     * Get kingdoms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKingdoms()
    {
        return $this->kingdoms;
    }

    /**
     * @return bool
     */
    public function shouldBeActivated()
    {
        $now = new \DateTime();
        if (!$this->getActive() &&
            $now > $this->getStartTime() &&
            $now < $this->getEndTime()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function shouldBeDeactivated()
    {
        $now = new \DateTime();
        if ($this->getActive() &&
            $now > $this->getEndTime()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isUpcoming()
    {
        return !$this->getActive() && time() < strtotime($this->startTime->format('Y-m-d h:i A'));
    }

    /**
     * @return bool
     */
    public function isEndingSoon()
    {
        $soon = (new \DateTime)->add(new \DateInterval('P3D'));

        return $this->getActive() && $soon > $this->getEndTime();
    }

    /**
     * @return bool
     */
    public function readyToPerformTick()
    {
        return $this->getMinutesSinceLastTick() >= $this->getTickInterval();
    }

    /**
     * @return World
     */
    public function performTick()
    {
        $this->addTick();
        $this->setMinutesSinceLastTick(0);

        return $this;
    }

    /**
     * @return World
     */
    public function skipTick()
    {
        $this->addMinuteSinceLastTick();

        return $this;
    }
}
