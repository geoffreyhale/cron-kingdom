<?php
namespace CronkdBundle\Entity\Event;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 *
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "attack"           = "AttackEvent",
 *     "birth"            = "BirthEvent",
 *     "kingdom_resource" = "KingdomResourceEvent",
 *     "net_worth"        = "NetWorthEvent",
 *     "probe"            = "ProbeEvent",
 * })
 */
abstract class Event extends BaseEntity
{
    const TYPE_BIRTH     = 'birth';
    const TYPE_QUEUE     = 'queue';
    const TYPE_DEQUEUE   = 'dequeue';
    const TYPE_NET_WORTH = 'net_worth';
    const TYPE_PROBE     = 'probe';

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
     * @ORM\Column(name="event_type", type="string", length=9)
     */
    private $eventType;

    /**
     * @var int
     *
     * @ORM\Column(name="tick", type="bigint")
     */
    private $tick;

    /**
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Kingdom")
     * @ORM\JoinColumn(name="kingdom_id", referencedColumnName="id")
     */
    private $kingdom;

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
     * Set eventType
     *
     * @param string $eventType
     *
     * @return Event
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get eventType
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Set tick
     *
     * @param integer $tick
     *
     * @return Event
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
     * Set kingdom
     *
     * @param Kingdom $kingdom
     *
     * @return Event
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
}
