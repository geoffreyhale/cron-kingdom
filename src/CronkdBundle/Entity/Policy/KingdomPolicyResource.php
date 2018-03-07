<?php
namespace CronkdBundle\Entity\Policy;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="kingdom_policy_resource")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class KingdomPolicyResource extends BaseEntity
{
    use PolicyTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Resource
     *
     * @ORM\ManyToOne(targetEntity="CronkdBundle\Entity\Resource\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    private $resource;

    /**
     * @var KingdomPolicy
     *
     * @ORM\ManyToOne(targetEntity="KingdomPolicy", inversedBy="resources")
     */
    private $policy;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set resource
     *
     * @param Resource $resource
     *
     * @return KingdomPolicyResource
     */
    public function setResource(Resource $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set policy
     *
     * @param KingdomPolicy $policy
     *
     * @return KingdomPolicyResource
     */
    public function setPolicy(KingdomPolicy $policy = null)
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
     * Set netWorthMultiplier
     *
     * @param float $netWorthMultiplier
     *
     * @return KingdomPolicyResource
     */
    public function setNetWorthMultiplier($netWorthMultiplier)
    {
        $this->netWorthMultiplier = $netWorthMultiplier;

        return $this;
    }

    /**
     * Get netWorthMultiplier
     *
     * @return float
     */
    public function getNetWorthMultiplier()
    {
        return $this->netWorthMultiplier;
    }
}
