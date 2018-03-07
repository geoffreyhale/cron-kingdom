<?php
namespace CronkdBundle\Entity\Policy;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\World;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="world_policy")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class WorldPolicy extends BaseEntity
{
    use PolicyTrait;

    const CONDITION_GREATER = 'greater';
    const CONDITION_RECENTLY_ATTACKED = 'attacked';
    const CONDITION_RECENTLY_DEFENDED = 'defended';

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
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="condition", type="string")
     */
    private $condition;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var Resource[]
     *
     * @ORM\ManyToMany(targetEntity="CronkdBundle\Entity\Resource\Resource")
     * @ORM\JoinTable(name="world_policy_resource",
     *      joinColumns={@ORM\JoinColumn(name="world_policy_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     */
    private $resources;

    /**
     * Individual resource multipliers.
     *
     * @var Resource[]
     *
     * @ORM\ManyToMany(targetEntity="CronkdBundle\Entity\Resource\Resource")
     * @ORM\JoinTable(name="world_policy_comparison_resource",
     *      joinColumns={@ORM\JoinColumn(name="world_policy_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     */
    private $comparisonResources;

    /**
     * Individual resource multipliers.
     *
     * @var PolicyResourceModifier[]
     *w
     * @ORM\OneToMany(targetEntity="WorldPolicyResource", mappedBy="policy")
     */
    private $resultingResources;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\World", inversedBy="policies")
     */
    private $world;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return WorldPolicy
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set condition
     *
     * @param string $condition
     *
     * @return WorldPolicy
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return WorldPolicy
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set world
     *
     * @param World $world
     *
     * @return WorldPolicy
     */
    public function setWorld(World $world = null)
    {
        $this->world = $world;

        return $this;
    }

    /**
     * Get world
     *
     * @return World
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Add resource
     *
     * @param Resource $resource
     *
     * @return WorldPolicy
     */
    public function addResource(Resource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param Resource $resource
     */
    public function removeResource(Resource $resource)
    {
        $this->resources->removeElement($resource);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add comparisonResource
     *
     * @param Resource $comparisonResource
     *
     * @return WorldPolicy
     */
    public function addComparisonResource(Resource $comparisonResource)
    {
        $this->comparisonResources[] = $comparisonResource;

        return $this;
    }

    /**
     * Remove comparisonResource
     *
     * @param Resource $comparisonResource
     */
    public function removeComparisonResource(Resource $comparisonResource)
    {
        $this->comparisonResources->removeElement($comparisonResource);
    }

    /**
     * Get comparisonResources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComparisonResources()
    {
        return $this->comparisonResources;
    }

    /**
     * Add resultingResource
     *
     * @param KingdomPolicyResource $resultingResource
     *
     * @return WorldPolicy
     */
    public function addResultingResource(KingdomPolicyResource $resultingResource)
    {
        $this->resultingResources[] = $resultingResource;

        return $this;
    }

    /**
     * Remove resultingResource
     *
     * @param KingdomPolicyResource $resultingResource
     */
    public function removeResultingResource(KingdomPolicyResource $resultingResource)
    {
        $this->resultingResources->removeElement($resultingResource);
    }

    /**
     * Get resultingResources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResultingResources()
    {
        return $this->resultingResources;
    }

    /**
     * @param PersistentCollection $kingdomResources
     * @return int
     */
    public function percentComplete(PersistentCollection $kingdomResources)
    {
        $resources = 0;
        $comparisonResources = 0;

        /** @var KingdomResource $kingdomResource */
        foreach ($kingdomResources as $kingdomResource) {
            if ($this->getResources()->contains($kingdomResource->getResource())) {
                $resources += $kingdomResource->getQuantity();
            } elseif ($this->getComparisonResources()->contains($kingdomResource->getResource())) {
                $comparisonResources += $kingdomResource->getQuantity();
            }
        }

        switch ($this->getCondition()) {
            case self::CONDITION_GREATER:
                if (0 == $comparisonResources) {
                    return 0 == $resources ? 0 : 100;
                }
                $completion = (int) (100 * ($resources / $comparisonResources));
                if ($completion > 100) {
                    $completion = 100;
                }

                return $completion;
            default:
                return 0;
        }
    }
}
