<?php
namespace CronkdBundle\Entity\Notification;

use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class ProbeNotification extends Notification
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
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="prober_id", referencedColumnName="id")
     */
    private $prober;

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
     * Set success
     *
     * @param boolean $success
     *
     * @return ProbeNotification
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
     * Set prober
     *
     * @param Kingdom $prober
     *
     * @return ProbeNotification
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
}
