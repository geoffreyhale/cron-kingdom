<?php
namespace CronkdBundle\Entity\Technology;

use CronkdBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="technology_level")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 *
 * @Jms\ExclusionPolicy("all")
 */
class TechnologyLevel extends BaseEntity
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
     * @ORM\Column(name="level", type="integer")
     *
     * @Jms\Expose()
     */
    private $level;

    /**
     * @var int
     *
     * @ORM\Column(name="cost", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $cost;

    /**
     * @var TechnologyLevel
     *
     * @ORM\ManyToOne(targetEntity="Technology", inversedBy="levels", cascade={"persist"})
     */
    private $technology;

    /**
     * @var
     *
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\KingdomTechnologyLevel", mappedBy="technologyLevel")
     */
    private $kingdoms;

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
     * Set level
     *
     * @param integer $level
     *
     * @return TechnologyLevel
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return TechnologyLevel
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set technology
     *
     * @param Technology $technology
     *
     * @return TechnologyLevel
     */
    public function setTechnology(Technology $technology = null)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return Technology
     */
    public function getTechnology()
    {
        return $this->technology;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add kingdom
     *
     * @param \CronkdBundle\Entity\KingdomTechnologyLevel $kingdom
     *
     * @return TechnologyLevel
     */
    public function addKingdom(\CronkdBundle\Entity\KingdomTechnologyLevel $kingdom)
    {
        $this->kingdoms[] = $kingdom;

        return $this;
    }

    /**
     * Remove kingdom
     *
     * @param \CronkdBundle\Entity\KingdomTechnologyLevel $kingdom
     */
    public function removeKingdom(\CronkdBundle\Entity\KingdomTechnologyLevel $kingdom)
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
}
