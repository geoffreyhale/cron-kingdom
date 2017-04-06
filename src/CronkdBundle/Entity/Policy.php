<?php
namespace CronkdBundle\Entity;

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
    const DEFENDER  = 'Defender';
    const ECONOMIST = 'Economist';
    const WARMONGER = 'Warmonger';

    const DEFENDER_BONUS  = 1.5;
    const WARMONGER_BONUS = 5;

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
     * @var KingdomPolicy[]
     *
     * @ORM\OneToMany(targetEntity="KingdomPolicy", mappedBy="policy")
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
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms = new ArrayCollection();
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
     * Add kingdom
     *
     * @param KingdomPolicy $kingdom
     *
     * @return Policy
     */
    public function addKingdom(KingdomPolicy $kingdom)
    {
        $this->kingdoms[] = $kingdom;

        return $this;
    }

    /**
     * Remove kingdom
     *
     * @param KingdomPolicy $kingdom
     */
    public function removeKingdom(KingdomPolicy $kingdom)
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
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
