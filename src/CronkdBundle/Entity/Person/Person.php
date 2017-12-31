<?php
namespace CronkdBundle\Entity\Person;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="person")
 * @ORM\Entity
 */
class Person extends BaseEntity
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
     */
    private $name;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom", inversedBy="persons")
     */
    private $kingdom;

    /**
     * @var int
     * @ORM\Column(name="attack", type="integer", options={"default": 0})
     */
    private $attack;

    /**
     * @var int
     * @ORM\Column(name="defense", type="integer", options={"default": 0})
     */
    private $defense;

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
     * @return Person
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
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return Person
     */
    public function setKingdom(Kingdom $kingdom = null)
    {
        $this->kingdom = $kingdom;
        return $this;
    }

    /**
     * Get kingdom
     *
     * @return Kingdom
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * @return int
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * @param int $attack
     * @return Person
     */
    public function setAttack($attack)
    {
        $this->attack = $attack;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * @param int $defense
     * @return Person
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;
        return $this;
    }
}