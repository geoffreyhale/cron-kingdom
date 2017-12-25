<?php
namespace CronkdBundle\Entity\Resource;

use CronkdBundle\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * ResourceHousing
 *
 * @ORM\Table(name="resource_housing")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 *
 */
class ResourceHousing extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="housing")
     */
    private $owningResource;

    /**
     * @ORM\ManyToOne(targetEntity="Resource")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
     */
    private $referencedResource;

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
     * Set owningResource
     *
     * @param Resource $owningResource
     *
     * @return ResourceHousing
     */
    public function setOwningResource(Resource $owningResource = null)
    {
        $this->owningResource = $owningResource;

        return $this;
    }

    /**
     * Get owningResource
     *
     * @return Resource
     */
    public function getOwningResource()
    {
        return $this->owningResource;
    }

    /**
     * Set referencedResource
     *
     * @param Resource $referencedResource
     *
     * @return ResourceHousing
     */
    public function setReferencedResource(Resource $referencedResource = null)
    {
        $this->referencedResource = $referencedResource;

        return $this;
    }

    /**
     * Get referencedResource
     *
     * @return Resource
     */
    public function getReferencedResource()
    {
        return $this->referencedResource;
    }
}
