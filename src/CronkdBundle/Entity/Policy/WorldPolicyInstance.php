<?php
namespace CronkdBundle\Entity\Policy;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="world_policy_instance")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\WorldPolicyInstanceRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class WorldPolicyInstance extends BaseEntity
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
     * @var KingdomPolicy
     *
     * @ORM\ManyToOne(targetEntity="WorldPolicy")
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
     * @return WorldPolicyInstance
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
     * @return WorldPolicyInstance
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
     * @return WorldPolicyInstance
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
     * @param WorldPolicy $policy
     *
     * @return WorldPolicyInstance
     */
    public function setPolicy(WorldPolicy $policy = null)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * Get policy
     *
     * @return WorldPolicy
     */
    public function getPolicy()
    {
        return $this->policy;
    }
}
