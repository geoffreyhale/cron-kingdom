<?php
namespace CronkdBundle\Entity\Tech;

use CronkdBundle\Entity\BaseEntity;
use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Table(name="policy_resource")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 */
class PolicyResource extends BaseEntity
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
     * @var Policy
     *
     * @ORM\ManyToOne(targetEntity="Policy", inversedBy="resources")
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
     * @return PolicyResource
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
     * @param Policy $policy
     *
     * @return PolicyResource
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

    /**
     * Set netWorthMultiplier
     *
     * @param float $netWorthMultiplier
     *
     * @return PolicyResource
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
