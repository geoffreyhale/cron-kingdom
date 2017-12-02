<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Policy\Policy;
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
    private $initialized = false;

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
     * @var int
     *
     * @ORM\Column(name="tick", type="bigint")
     */
    private $tick = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_tick_time", type="datetime", nullable=true)
     */
    private $lastTickTime = null;

    /**
     * Tick interval in minutes.
     *
     * @var int
     *
     * @ORM\Column(name="tick_interval", type="integer")
     * @Assert\Range(min=1, minMessage="Interval must be greater than zero.")
     */
    private $tickInterval = 60;

    /**
     * Birth rate as a percentage.
     *
     * @var int
     *
     * @ORM\Column(name="birth_rate", type="integer")
     * @Assert\Range(min=1, minMessage="Birth rate must be greater than zero.")
     */
    private $birthRate = 1;

    /**
     * Number of ticks a policy lasts.
     *
     * @var int
     *
     * @ORM\Column(name="policy_duration", type="integer")
     * @Assert\Range(min=1, minMessage="Policy duration must be greater than zero.")"
     */
    private $policyDuration = 24;

    /**
     * @var Kingdom[]
     *
     * @ORM\OneToMany(targetEntity="Kingdom", mappedBy="world")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $kingdoms;

    /**
     * @var Policy[]
     *
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\Policy\Policy", mappedBy="world")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $policies;

    /**
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\Resource\Resource", mappedBy="world")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $resources;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms  = new ArrayCollection();
        $this->policies  = new ArrayCollection();
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
     * Set tickTime
     *
     * @param \DateTime $lastTickTime
     *
     * @return World
     */
    public function setLastTickTime($lastTickTime)
    {
        $this->lastTickTime = $lastTickTime;

        return $this;
    }

    /**
     * Get tickTime
     *
     * @return \DateTime
     */
    public function getLastTickTime()
    {
        return $this->lastTickTime;
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
     * Set birthRate
     *
     * @param integer $birthRate
     *
     * @return World
     */
    public function setBirthRate($birthRate)
    {
        $this->birthRate = $birthRate;

        return $this;
    }

    /**
     * Get birthRate
     *
     * @return integer
     */
    public function getBirthRate()
    {
        return $this->birthRate;
    }

    /**
     * Set policyDuration
     *
     * @param integer $policyDuration
     *
     * @return World
     */
    public function setPolicyDuration($policyDuration)
    {
        $this->policyDuration = $policyDuration;

        return $this;
    }

    /**
     * Get policyDuration
     *
     * @return integer
     */
    public function getPolicyDuration()
    {
        return $this->policyDuration;
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
     * Add policy
     *
     * @param Policy $policy
     *
     * @return World
     */
    public function addPolicy(Policy $policy)
    {
        $this->policies[] = $policy;

        return $this;
    }

    /**
     * Remove policy
     *
     * @param Policy $policy
     */
    public function removePolicy(Policy $policy)
    {
        $this->policies->removeElement($policy);
    }

    /**
     * Get policies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPolicies()
    {
        return $this->policies;
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
        return !$this->getInitialized() && $this->isActive();
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
        if (null === $this->getLastTickTime()) {
            return true;
        }

        $diff = (new \DateTime())->diff($this->getLastTickTime());

        $minutes = $diff->days * 24 * 60;
        $minutes += $diff->h * 60;
        $minutes += $diff->i;

        return $minutes >= $this->getTickInterval();
    }

    /**
     * @return World
     */
    public function performTick()
    {
        $this->setLastTickTime(new \DateTime());
        $this->addTick();

        return $this;
    }
}
