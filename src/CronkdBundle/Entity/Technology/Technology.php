<?php
namespace CronkdBundle\Entity\Technology;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\World;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="technology")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 *
 * @Jms\ExclusionPolicy("all")
 */
class Technology extends BaseEntity
{
    const TYPE_ECONOMY     = 'economy';
    const TYPE_WAR         = 'war';
    const TYPES = [
        self::TYPE_ECONOMY => 'Economy',
        self::TYPE_WAR     => 'War',
    ];

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
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Jms\Expose()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     *
     * @Jms\Expose()
     */
    private $type;

    /**
     * @var TechnologyLevel[]
     *
     * @ORM\OneToMany(targetEntity="TechnologyLevel", mappedBy="technology", cascade={"persist"})
     * @ORM\OrderBy({"level": "ASC"})
     */
    private $levels;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\World", inversedBy="technologies", cascade={"persist"})
     */
    private $world;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->levels = new ArrayCollection();
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
     * @return Technology
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
     * Set type
     *
     * @param string $type
     *
     * @return Technology
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
     * Add level
     *
     * @param TechnologyLevel $level
     *
     * @return Technology
     */
    public function addLevel(TechnologyLevel $level)
    {
        $this->levels[] = $level;

        return $this;
    }

    /**
     * Remove level
     *
     * @param TechnologyLevel $level
     */
    public function removeLevel(TechnologyLevel $level)
    {
        $this->levels->removeElement($level);
    }

    /**
     * Get levels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * Set world
     *
     * @param World $world
     *
     * @return Technology
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
}
