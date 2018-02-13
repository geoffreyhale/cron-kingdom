<?php
namespace CronkdBundle\Entity\Notification;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Kingdom;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="tech")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 *
 * @ORM\MappedSuperclass()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "policy" = "Policy",
 *     "skill"  = "Skill",
 * })
 */
abstract class Tech extends BaseEntity
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * Individual resource multipliers.
     *
     * @var PolicyResourceModifier[]
     *
     * @ORM\OneToMany(targetEntity="PolicyResource", mappedBy="policy")
     */
    private $resources;
}
