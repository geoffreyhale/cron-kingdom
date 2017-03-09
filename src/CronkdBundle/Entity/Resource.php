<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Resource
 *
 * @ORM\Table(name="resource")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\ResourceRepository")
 */
class Resource
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
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="resource")
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
}
