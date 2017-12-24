<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Model\AttackReport;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\Event\AttackResultEventRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class AttackResultEvent extends Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    /**
     * @var string
     *
     * @ORM\Column(name="report_data", type="text")
     */
    private $reportData;

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
     * @return AttackResultEvent
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
     * Set reportData
     *
     * @param string $reportData
     *
     * @return AttackReport
     */
    public function setReportData($reportData)
    {
        $this->reportData = $reportData;

        return $this;
    }

    /**
     * Get reportData
     *
     * @return string
     */
    public function getReportData()
    {
        return $this->reportData;
    }

    /**
     * Set attacker
     *
     * @param Kingdom $attacker
     *
     * @return AttackResultEvent
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
     * @return AttackResultEvent
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
