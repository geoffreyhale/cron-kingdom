<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * Resource
 *
 * @ORM\Table(name="resource_type")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\ResourceRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class ResourceType extends BaseEntity
{
    // @TODO: don't hard code these
    const POPULATION = 'Population';
    const MATERIAL   = 'Material';
    const BUILDING   = 'Building';

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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Resource", mappedBy="type")
     */
    private $resources;

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
     * @return Resource
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
     * Constructor
     */
    public function __construct()
    {
        $this->resources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add resource
     *
     * @param \CronkdBundle\Entity\Resource $resource
     *
     * @return ResourceType
     */
    public function addResource(\CronkdBundle\Entity\Resource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param \CronkdBundle\Entity\Resource $resource
     */
    public function removeResource(\CronkdBundle\Entity\Resource $resource)
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
}
