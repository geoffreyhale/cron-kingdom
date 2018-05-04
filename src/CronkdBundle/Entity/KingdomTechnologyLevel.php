<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Technology\TechnologyLevel;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * KingdomTechnologyLevel
 *
 * @ORM\Table(name="kingdom_technology_level")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class KingdomTechnologyLevel extends BaseEntity
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
     * @var Kingdom
     *
     * @ORM\ManyToOne(targetEntity="Kingdom", inversedBy="resources")
     */
    private $kingdom;

    /**
     * @var TechnologyLevel
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Technology\TechnologyLevel", inversedBy="kingdoms")
     */
    private $technologyLevel;

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
     * @return KingdomTechnologyLevel
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
     * Set technologyLevel
     *
     * @param TechnologyLevel $technologyLevel
     *
     * @return KingdomTechnologyLevel
     */
    public function setTechnologyLevel(TechnologyLevel $technologyLevel = null)
    {
        $this->technologyLevel = $technologyLevel;

        return $this;
    }

    /**
     * Get technologyLevel
     *
     * @return TechnologyLevel
     */
    public function getTechnologyLevel()
    {
        return $this->technologyLevel;
    }
}
