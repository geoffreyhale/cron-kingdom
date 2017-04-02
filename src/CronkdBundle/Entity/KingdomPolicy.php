<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="kingdom_policy")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomPolicyRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class KingdomPolicy extends BaseEntity
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime")
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime")
     */
    private $endTime;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom", inversedBy="policies")
     */
    private $kingdom;

    /**
     * @var Policy
     *
     * @ORM\ManyToOne(targetEntity="Policy", inversedBy="kingdoms")
     */
    private $policy;

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
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return KingdomPolicy
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
     * Set policy
     *
     * @param Policy $policy
     *
     * @return KingdomPolicy
     */
    public function setPolicy(Policy $policy = null)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return KingdomPolicy
     */
    public function getPolicy()
    {
        return $this->policy;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     *
     * @return KingdomPolicy
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     *
     * @return KingdomPolicy
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }
}
