<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class MapTile extends BaseEntity
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
     * @ORM\Column(name="x", type="integer")
     */
    private $x;

    /**
     * @var int
     *
     * @ORM\Column(name="y", type="integer")
     */
    private $y;

    /**
     * @var Kingdom[]
     *
     * @ORM\OneToMany(targetEntity="Kingdom", mappedBy="mapTile")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $kingdoms;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->kingdoms  = new ArrayCollection();
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
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     * @return self
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param int $y
     * @return self
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * Add kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return self
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
}
