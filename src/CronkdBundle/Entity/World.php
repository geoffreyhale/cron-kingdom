<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * World
 *
 * @ORM\Table(name="world")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\WorldRepository")
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
    public function addTick()
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
}
