<?php
namespace CronkdBundle\Entity\Resource;

use CronkdBundle\Entity\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ResourceAction
 *
 * @ORM\Table(name="resource_action")
 * @ORM\Entity()
 *
 * @Jms\ExclusionPolicy("all")
 *
 */
class ResourceAction extends BaseEntity
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
     * @ORM\Column(name="verb", type="string", length=255)
     * @Jms\Expose()
     * @Assert\NotBlank()
     */
    private $verb;

    /**
     * @var int
     *
     * @ORM\Column(name="output_quantity", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Quantity cannot be negative")
     */
    private $outputQuantity;

    /**
     * @var int
     *
     * @ORM\Column(name="queue_size", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Queue size cannot be negative")
     */
    private $queueSize;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @Jms\Expose()
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="actions")
     */
    private $resource;

    /**
     * @ORM\OneToMany(targetEntity="ResourceActionInput", mappedBy="resourceAction", cascade={"persist", "remove"})
     */
    private $inputs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inputs = new ArrayCollection();
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
     * Set verb
     *
     * @param string $verb
     *
     * @return ResourceAction
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;

        return $this;
    }

    /**
     * Get verb
     *
     * @return string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Set outputQuantity
     *
     * @param integer $outputQuantity
     *
     * @return ResourceAction
     */
    public function setOutputQuantity($outputQuantity)
    {
        $this->outputQuantity = $outputQuantity;

        return $this;
    }

    /**
     * Get outputQuantity
     *
     * @return integer
     */
    public function getOutputQuantity()
    {
        return $this->outputQuantity;
    }

    /**
     * Set queueSize
     *
     * @param integer $queueSize
     *
     * @return ResourceAction
     */
    public function setQueueSize($queueSize)
    {
        $this->queueSize = $queueSize;

        return $this;
    }

    /**
     * Get queueSize
     *
     * @return integer
     */
    public function getQueueSize()
    {
        return $this->queueSize;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ResourceAction
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
     * Set resource
     *
     * @param Resource $resource
     *
     * @return ResourceAction
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
     * Add input
     *
     * @param ResourceActionInput $input
     *
     * @return ResourceAction
     */
    public function addInput(ResourceActionInput $input)
    {
        $this->inputs[] = $input;

        return $this;
    }

    /**
     * Remove input
     *
     * @param ResourceActionInput $input
     */
    public function removeInput(ResourceActionInput $input)
    {
        $this->inputs->removeElement($input);
    }

    /**
     * Get inputs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @param array $excludedResources
     * @return array
     */
    public function getUnavailableResourceInputIds(array $excludedResources = [])
    {
        $unavailableIds = [];

        /** @var ResourceActionInput $input */
        foreach ($this->getInputs() as $input) {
            $resourceId = $input->getResource()->getId();
            if (!in_array($resourceId, $excludedResources)) {
                $unavailableIds[] = $resourceId;
            }
        }

        return $unavailableIds;
    }
}
