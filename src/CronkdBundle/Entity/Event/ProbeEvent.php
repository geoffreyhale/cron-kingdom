<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class ProbeEvent extends Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="success", type="boolean")
     *
     * @Jms\Expose()
     */
    private $success;

    /**
     * @var string
     *
     * @ORM\Column(name="report_data", type="text")
     *
     * @Jms\Expose()
     */
    private $reportData;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="attacker_id", referencedColumnName="id")
     *
     * @Jms\Expose()
     */
    private $prober;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="defender_id", referencedColumnName="id")
     *
     * @Jms\Expose()
     */
    private $probee;

    /**
     * Set success
     *
     * @param boolean $success
     *
     * @return ProbeEvent
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
     * @return ProbeEvent
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
        return json_decode($this->reportData, true);
    }

    /**
     * Set prober
     *
     * @param Kingdom $prober
     *
     * @return ProbeEvent
     */
    public function setProber(Kingdom $prober = null)
    {
        $this->prober = $prober;

        return $this;
    }

    /**
     * Get prober
     *
     * @return Kingdom
     */
    public function getProber()
    {
        return $this->prober;
    }

    /**
     * Set probee
     *
     * @param Kingdom $probee
     *
     * @return ProbeEvent
     */
    public function setProbee(Kingdom $probee = null)
    {
        $this->probee = $probee;

        return $this;
    }

    /**
     * Get probee
     *
     * @return Kingdom
     */
    public function getProbee()
    {
        return $this->probee;
    }
}
