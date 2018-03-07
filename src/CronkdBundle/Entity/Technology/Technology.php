<?php
namespace CronkdBundle\Entity;

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

    private $type;

    /**
     * @var TechnologyLevel[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="kingdom", cascade={"persist"})
     * @ORM\OrderBy({"level": "ASC"})
     */
    private $levels;

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
}
