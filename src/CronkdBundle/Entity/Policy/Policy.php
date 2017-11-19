<?php
namespace CronkdBundle\Entity\Policy;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\World;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="policy")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class Policy extends BaseEntity
{
    use PolicyTrait;

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
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * Individual resource multipliers.
     *
     * @var PolicyResourceModifier[]
     *
     * @ORM\OneToMany(targetEntity="PolicyResource", mappedBy="policy")
     */
    private $resources;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\World", inversedBy="policies")
     */
    private $world;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set name
     *
     * @param string $name
     *
     * @return Policy
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
     * Set description
     *
     * @param string $description
     *
     * @return Policy
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
     * Add resource
     *
     * @param PolicyResource $resource
     *
     * @return Policy
     */
    public function addResource(PolicyResource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param PolicyResource $resource
     */
    public function removeResource(PolicyResource $resource)
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
     * Set world
     *
     * @param World $world
     *
     * @return Policy
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
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set netWorthMultiplier
     *
     * @param float $netWorthMultiplier
     *
     * @return Policy
     */
    public function setNetWorthMultiplier($netWorthMultiplier)
    {
        $this->netWorthMultiplier = $netWorthMultiplier;

        return $this;
    }

    /**
     * Get netWorthMultiplier
     *
     * @return float
     */
    public function getNetWorthMultiplier()
    {
        return $this->netWorthMultiplier;
    }
}
