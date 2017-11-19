<?php
namespace CronkdBundle\Entity\Resource;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\World;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Resource
 *
 * @ORM\Table(name="resource")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\Resource\ResourceRepository")
 *
 * @Jms\ExclusionPolicy("all")
 *
 * @UniqueEntity(
 *      fields={"name", "world"},
 *      errorPath="name",
 *      message="This resource name already exists."
 * )
 */
class Resource extends BaseEntity
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Jms\Expose()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="definition", type="string", length=255)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Value cannot be negative")
     */
    private $value = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_be_probed", type="boolean")
     *
     * @Jms\Expose()
     */
    private $canBeProbed = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_be_produced", type="boolean")
     *
     * @Jms\Expose()
     */
    private $canBeProduced = false;

    /**
     * @var int
     *
     * @ORM\Column(name="probe_power", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Probe Power cannot be negative")
     */
    private $probePower = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Capacity cannot be negative")
     */
    private $capacity = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="attack", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Attack Power cannot be negative")
     */
    private $attack = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="defense", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Defense Power cannot be negative")
     */
    private $defense = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="starting_amount", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Starting amount cannot be negative")
     */
    private $startingAmount = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="spoil_of_war", type="boolean")
     *
     * @Jms\Expose()
     */
    private $spoilOfWar = false;

    /**
     * @var int
     *
     * @ORM\Column(name="spoil_of_war_capture_percentage", type="integer")
     */
    private $spoilOfWarCapturePercentage = 0;

    /**
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\KingdomResource", mappedBy="resource")
     */
    private $kingdoms;

    /**
     * @var ResourceType
     *
     * @ORM\ManyToOne(targetEntity="ResourceType", inversedBy="resources")
     */
    private $type;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\World", inversedBy="resources")
     */
    private $world;

    /**
     * @var ResourceAction[]
     *
     * @ORM\OneToMany(targetEntity="ResourceAction", mappedBy="resource")
     */
    private $actions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actions  = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Resource
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
     * Set value
     *
     * @param integer $value
     *
     * @return Resource
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set canBeProbed
     *
     * @param boolean $canBeProbed
     *
     * @return Resource
     */
    public function setCanBeProbed($canBeProbed)
    {
        $this->canBeProbed = $canBeProbed;

        return $this;
    }

    /**
     * Get canBeProbed
     *
     * @return boolean
     */
    public function getCanBeProbed()
    {
        return $this->canBeProbed;
    }

    /**
     * Set attack
     *
     * @param integer $attack
     *
     * @return Resource
     */
    public function setAttack($attack)
    {
        $this->attack = $attack;

        return $this;
    }

    /**
     * Get attack
     *
     * @return integer
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     *
     * @return Resource
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * Get defense
     *
     * @return integer
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * Set canBeProduced
     *
     * @param boolean $canBeProduced
     *
     * @return Resource
     */
    public function setCanBeProduced($canBeProduced)
    {
        $this->canBeProduced = $canBeProduced;

        return $this;
    }

    /**
     * Get canBeProduced
     *
     * @return boolean
     */
    public function getCanBeProduced()
    {
        return $this->canBeProduced;
    }

    /**
     * Set capacity
     *
     * @param integer $capacity
     *
     * @return Resource
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity
     *
     * @return integer
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set probePower
     *
     * @param integer $probePower
     *
     * @return Resource
     */
    public function setProbePower($probePower)
    {
        $this->probePower = $probePower;

        return $this;
    }

    /**
     * Get probePower
     *
     * @return integer
     */
    public function getProbePower()
    {
        return $this->probePower;
    }

    /**
     * Set startingAmount
     *
     * @param integer $startingAmount
     *
     * @return Resource
     */
    public function setStartingAmount($startingAmount)
    {
        $this->startingAmount = $startingAmount;

        return $this;
    }

    /**
     * Get startingAmount
     *
     * @return integer
     */
    public function getStartingAmount()
    {
        return $this->startingAmount;
    }

    /**
     * Set spoilOfWar
     *
     * @param boolean $spoilOfWar
     *
     * @return Resource
     */
    public function setSpoilOfWar($spoilOfWar)
    {
        $this->spoilOfWar = $spoilOfWar;

        return $this;
    }

    /**
     * Get spoilOfWar
     *
     * @return boolean
     */
    public function isSpoilOfWar()
    {
        return $this->spoilOfWar;
    }

    /**
     * Add kingdom
     *
     * @param KingdomResource $kingdom
     *
     * @return Resource
     */
    public function addKingdom(KingdomResource $kingdom)
    {
        $this->kingdoms[] = $kingdom;

        return $this;
    }

    /**
     * Remove kingdom
     *
     * @param KingdomResource $kingdom
     */
    public function removeKingdom(KingdomResource $kingdom)
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
     * Set type
     *
     * @param ResourceType $type
     *
     * @return Resource
     */
    public function setType(ResourceType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return ResourceType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set world
     *
     * @param World $world
     *
     * @return Resource
     */
    public function setWorld(World $world = null)
    {
        $this->world = $world;

        return $this;
    }

    /**
     * Get world
     *
     * @return World
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * Get spoilOfWar
     *
     * @return boolean
     */
    public function getSpoilOfWar()
    {
        return $this->spoilOfWar;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ResourceAction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add action
     *
     * @param ResourceAction $action
     *
     * @return Resource
     */
    public function addAction(ResourceAction $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Remove action
     *
     * @param ResourceAction $action
     */
    public function removeAction(ResourceAction $action)
    {
        $this->actions->removeElement($action);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set spoilOfWarCapturePercentage
     *
     * @param integer $spoilOfWarCapturePercentage
     *
     * @return Resource
     */
    public function setSpoilOfWarCapturePercentage($spoilOfWarCapturePercentage)
    {
        $this->spoilOfWarCapturePercentage = $spoilOfWarCapturePercentage;

        return $this;
    }

    /**
     * Get spoilOfWarCapturePercentage
     *
     * @return integer
     */
    public function getSpoilOfWarCapturePercentage()
    {
        return $this->spoilOfWarCapturePercentage;
    }
}
