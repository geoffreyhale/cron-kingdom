<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="attack_log")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\AttackLogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class AttackLog extends BaseEntity
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
     * @ORM\Column(name="tick", type="integer")
     */
    private $tick;

    /**
     * @var int
     *
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom")
     * @ORM\JoinColumn(name="attacker_id", referencedColumnName="id")
     */
    private $attacker;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom")
     * @ORM\JoinColumn(name="defender_id", referencedColumnName="id")
     */
    private $defender;

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
     * Set tick
     *
     * @param integer $tick
     *
     * @return Queue
     */
    public function setTick($tick)
    {
        $this->tick = $tick;

        return $this;
    }

    /**
     * Get tick
     *
     * @return int
     */
    public function getTick()
    {
        return $this->tick;
    }

    /**
     * Set success
     *
     * @param boolean $success
     *
     * @return AttackLog
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
     * @return AttackLog
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
     * @return AttackLog
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
