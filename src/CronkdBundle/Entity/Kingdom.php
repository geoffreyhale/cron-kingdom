<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Kingdom
 *
 * @ORM\Table(name="kingdom")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomRepository")
 */
class Kingdom
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
     */
    private $name;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="World", inversedBy="kingdoms")
     */
    private $world;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="kingdoms")
     */
    private $user;

    /**
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="kingdom", fetch="EAGER")
     */
    private $resources;

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
     * @return Kingdom
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
     * Set world
     *
     * @param World $world
     *
     * @return Kingdom
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
     * Set user
     *
     * @param User $user
     *
     * @return Kingdom
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add resource
     *
     * @param KingdomResource $resource
     *
     * @return Kingdom
     */
    public function addResource(KingdomResource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param KingdomResource $resource
     */
    public function removeResource(KingdomResource $resource)
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
}
