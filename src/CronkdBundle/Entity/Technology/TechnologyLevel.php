<?php
namespace CronkdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="technology")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 *
 * @Jms\ExclusionPolicy("all")
 */
class TechnologyLevel extends BaseEntity
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
     * @var string
     *
     * @ORM\Column(name="level", type="integer")
     *
     * @Jms\Expose()
     */
    private $level;

    /**
     * @var int
     *
     * @ORM\Column(name="cost", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $cost;

    /**
     * @var Technology
     *
     * @ORM\ManyToOne(targetEntity="Technology", inversedBy="levels", cascade={"persist"})
     */
    private $technology;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
