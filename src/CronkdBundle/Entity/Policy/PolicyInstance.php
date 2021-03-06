<?php
namespace CronkdBundle\Entity\Policy;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="policy_instance")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class PolicyInstance extends BaseEntity
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
     * @ORM\Column(name="start_tick", type="integer")
     */
    private $startTick;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tick_duration", type="integer")
     */
    private $tickDuration;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom", inversedBy="policies")
     */
    private $kingdom;

    /**
     * @var Policy
     *
     * @ORM\ManyToOne(targetEntity="Policy")
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
     * Set startTick
     *
     * @param integer $startTick
     *
     * @return PolicyInstance
     */
    public function setStartTick($startTick)
    {
        $this->startTick = $startTick;

        return $this;
    }

    /**
     * Get startTick
     *
     * @return integer
     */
    public function getStartTick()
    {
        return $this->startTick;
    }

    /**
     * Set tickDuration
     *
     * @param integer $tickDuration
     *
     * @return PolicyInstance
     */
    public function setTickDuration($tickDuration)
    {
        $this->tickDuration = $tickDuration;

        return $this;
    }

    /**
     * Get tickDuration
     *
     * @return integer
     */
    public function getTickDuration()
    {
        return $this->tickDuration;
    }

    /**
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return PolicyInstance
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
     * @return PolicyInstance
     */
    public function setPolicy(Policy $policy = null)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return Policy
     */
    public function getPolicy()
    {
        return $this->policy;
    }
}
