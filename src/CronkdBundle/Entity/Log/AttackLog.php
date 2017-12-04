<?php
namespace CronkdBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\Log\AttackLogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class AttackLog extends Log
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
     * @param \CronkdBundle\Entity\Kingdom $attacker
     *
     * @return AttackLog
     */
    public function setAttacker(\CronkdBundle\Entity\Kingdom $attacker = null)
    {
        $this->attacker = $attacker;

        return $this;
    }

    /**
     * Get attacker
     *
     * @return \CronkdBundle\Entity\Kingdom
     */
    public function getAttacker()
    {
        return $this->attacker;
    }

    /**
     * Set defender
     *
     * @param \CronkdBundle\Entity\Kingdom $defender
     *
     * @return AttackLog
     */
    public function setDefender(\CronkdBundle\Entity\Kingdom $defender = null)
    {
        $this->defender = $defender;

        return $this;
    }

    /**
     * Get defender
     *
     * @return \CronkdBundle\Entity\Kingdom
     */
    public function getDefender()
    {
        return $this->defender;
    }
}
