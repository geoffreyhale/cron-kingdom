<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * Resource
 *
 * @ORM\Table(name="resource")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\ResourceRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class Resource extends BaseEntity
{
    const CIVILIAN = 'Civilian';
    const MATERIAL = 'Material';
    const HOUSING  = 'Housing';
    const MILITARY = 'Military';
    const HACKER   = 'Hacker';

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
     *
     * @Jms\Expose()
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     *
     * @Jms\Expose()
     */
    private $value;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_be_probed", type="boolean")
     *
     * @Jms\Expose()
     */
    private $canBeProbed;

    /**
     * @var int
     *
     * @ORM\Column(name="attack", type="integer")
     *
     * @Jms\Expose()
     */
    private $attack;

    /**
     * @var int
     *
     * @ORM\Column(name="defense", type="integer")
     *
     * @Jms\Expose()
     */
    private $defense;

    /**
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="resource")
     */
    private $kingdoms;

    /**
     * @ORM\ManyToOne(targetEntity="ResourceType", inversedBy="resources")
     */
    private $type;

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
     * Set type
     *
     * @param \CronkdBundle\Entity\ResourceType $type
     *
     * @return Resource
     */
    public function setType(\CronkdBundle\Entity\ResourceType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \CronkdBundle\Entity\ResourceType
     */
    public function getType()
    {
        return $this->type;
    }
}
