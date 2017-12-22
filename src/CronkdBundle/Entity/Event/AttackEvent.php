<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\Event\AttackEventRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class AttackEvent extends Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="attacker_id", referencedColumnName="id")
     */
    private $attacker;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="defender_id", referencedColumnName="id")
     */
    private $defender;

    /**
     * Set success
     *
     * @param boolean $success
     *
     * @return AttackEvent
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get success
     *
     * @return boolean
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * Set attacker
     *
     * @param Kingdom $attacker
     *
     * @return AttackEvent
     */
    public function setAttacker(Kingdom $attacker = null)
    {
        $this->attacker = $attacker;

        return $this;
    }

    /**
     * Get attacker
     *
     * @return Kingdom
     */
    public function getAttacker()
    {
        return $this->attacker;
    }

    /**
     * Set defender
     *
     * @param Kingdom $defender
     *
     * @return AttackEvent
     */
    public function setDefender(Kingdom $defender = null)
    {
        $this->defender = $defender;

        return $this;
    }

    /**
     * Get defender
     *
     * @return Kingdom
     */
    public function getDefender()
    {
        return $this->defender;
    }
}
