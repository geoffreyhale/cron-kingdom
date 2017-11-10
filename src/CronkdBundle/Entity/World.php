<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Resource\Resource;
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
     * @ORM\Column(name="initialized", type="boolean", options={"default": 0})
     */
    private $initialized;

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
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     *
     * @Assert\Expression(
     *     "this.getStartTime() <= this.getEndTime()",
     *     message="End time must be later than start time!"
     * )
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
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\Resource\Resource", mappedBy="world")
     */
    private $resources;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms  = new ArrayCollection();
        $this->resources = new ArrayCollection();
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
     * Set initialized
     *
     * @param boolean $initialized
     *
     * @return World
     */
    public function setInitialized($initialized)
    {
        $this->initialized = $initialized;

        return $this;
    }

    /**
     * Get initialized
     *
     * @return boolean
     */
    public function getInitialized()
    {
        return $this->initialized;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return World
     */
    public function setDefaultInitialized()
    {
        if (null === $this->getInitialized()) {
            return $this->setInitialized(false);
        }

        return $this;
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
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return World
     */
    public function setStartTime(\DateTime $startTime)
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
    public function setEndTime(\DateTime $endTime)
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
     * 1 minute is the minimum value for tick interval.
     *
     * @param integer $tickInterval
     *
     * @return World
     */
    public function setTickInterval(int $tickInterval)
    {
        if (0 >= $tickInterval) {
            $tickInterval = 1;
        }

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
        if (null === $this->getMinutesSinceLastTick()) {
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
     * Add resource
     *
     * @param Resource $resource
     *
     * @return World
     */
    public function addResource(Resource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param Resource $resource
     */
    public function removeResource(Resource $resource)
    {
        $this->resources->removeElement($resource);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return bool
     */
    public function isUpcoming()
    {
        $now = new \DateTime();

        return null !== $this->getStartTime() && $now < $this->getStartTime();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if (null === $this->getStartTime()) {
            return false;
        }

        $now = new \DateTime();
        $isNowBeforeEnd = null === $this->getEndTime() || $now < $this->getEndTime();

        return $now > $this->getStartTime() && $isNowBeforeEnd;
    }

    /**
     * @return bool
     */
    public function isInactive()
    {
        if (null === $this->getStartTime()) {
            return false;
        }

        $now = new \DateTime();

        return null !== $this->getEndTime() && $now > $this->getEndTime();
    }

    /**
     * @return bool
     */
    public function shouldBeInitialized()
    {
        if (!$this->getInitialized() && $this->isActive()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEndingSoon()
    {
        if (null === $this->getStartTime()) {
            return false;
        }

        $soon = (new \DateTime)->add(new \DateInterval('P3D'));

        return $this->isActive() && $soon > $this->getEndTime();
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
    public function incrementTimeSinceLastTick()
    {
        $this->addMinuteSinceLastTick();

        return $this;
    }
}
