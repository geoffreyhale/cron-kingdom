<?php
namespace CronkdBundle\Entity\Resource;

use CronkdBundle\Entity\BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ResourceActionInput
 *
 * @ORM\Table(name="resource_action_input")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 *
 * @Jms\ExclusionPolicy("all")
 */
class ResourceActionInput extends BaseEntity
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
     * @var int
     *
     * @ORM\Column(name="input_quantity", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Quantity cannot be negative")
     */
    private $inputQuantity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="requeue", type="boolean")
     */
    private $requeue;

    /**
     * @var Resource
     *
     * @ORM\ManyToOne(targetEntity="Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     */
    private $resource;

    /**
     * @var int
     *
     * @ORM\Column(name="queue_size", type="integer")
     * @Jms\Expose()
     * @Assert\Range(min=0, minMessage="Queue size cannot be negative")
     */
    private $queueSize;

    /**
     * @ORM\ManyToOne(targetEntity="ResourceAction", inversedBy="inputs")
     */
    private $resourceAction;

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
     * Set inputQuantity
     *
     * @param integer $inputQuantity
     *
     * @return ResourceActionInput
     */
    public function setInputQuantity($inputQuantity)
    {
        $this->inputQuantity = $inputQuantity;

        return $this;
    }

    /**
     * Get inputQuantity
     *
     * @return integer
     */
    public function getInputQuantity()
    {
        return $this->inputQuantity;
    }

    /**
     * Set requeue
     *
     * @param boolean $requeue
     *
     * @return ResourceActionInput
     */
    public function setRequeue($requeue)
    {
        $this->requeue = $requeue;

        return $this;
    }

    /**
     * Get requeue
     *
     * @return boolean
     */
    public function getRequeue()
    {
        return $this->requeue;
    }

    /**
     * Set queueSize
     *
     * @param integer $queueSize
     *
     * @return ResourceActionInput
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
     * @ORM\PreUpdate()
     * @ORM\PrePersist()
     * @return ResourceActionInput
     */
    public function setDefaultQueueSize()
    {
        if (null === $this->queueSize) {
            return $this->getRequeue() ? $this->setQueueSize(1) : $this->setQueueSize(0);
        }
    }

    /**
     * Set resource
     *
     * @param Resource $resource
     *
     * @return ResourceActionInput
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
     * Set resourceAction
     *
     * @param ResourceAction $resourceAction
     *
     * @return ResourceActionInput
     */
    public function setResourceAction(ResourceAction $resourceAction = null)
    {
        $this->resourceAction = $resourceAction;

        return $this;
    }

    /**
     * Get resourceAction
     *
     * @return ResourceAction
     */
    public function getResourceAction()
    {
        return $this->resourceAction;
    }
}
