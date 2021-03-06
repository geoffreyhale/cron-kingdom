<?php
namespace CronkdBundle\Entity;

use CronkdBundle\Entity\Policy\PolicyInstance;
use CronkdBundle\Entity\Resource\Resource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Kingdom
 *
 * @ORM\Table(name="kingdom")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomRepository")
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Jms\ExclusionPolicy("all")
 */
class Kingdom extends BaseEntity
{
    const DEFAULT_ELO = 1200;

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
     * @ORM\Column(name="elo", type="integer", options={"default": 1200})
     *
     * @Jms\Expose()
     */
    private $elo;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Jms\Expose()
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="net_worth", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $netWorth;

    /**
     * @var int
     *
     * @ORM\Column(name="liquidity", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $liquidity;

    /**
     * @var int
     *
     * @ORM\Column(name="attack", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $attack;

    /**
     * @var int
     *
     * @ORM\Column(name="defense", type="bigint", options={"default": 0})
     *
     * @Jms\Expose()
     */
    private $defense;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="World", inversedBy="kingdoms")
     */
    private $world;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="kingdoms")
     */
    private $user;

    /**
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="kingdom", cascade={"persist"})
     */
    private $resources;

    /**
     * @var Queue[]
     *
     * @ORM\OneToMany(targetEntity="Queue", mappedBy="kingdom", fetch="EAGER")
     */
    private $queues;

    /**
     * @var PolicyInstance[]
     *
     * @ORM\OneToMany(targetEntity="CronkdBundle\Entity\Policy\PolicyInstance", mappedBy="kingdom")
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $policies;

    /**
     * @var ChatMessage
     *
     * @ORM\ManyToOne(targetEntity="ChatMessage")
     */
    private $lastReadChatMessage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->policies  = new ArrayCollection();
        $this->queues    = new ArrayCollection();
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
     * Get ELO
     *
     * @return int
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Set ELO
     *
     * @param int $elo
     *
     * @return Kingdom
     */
    public function setElo(int $elo)
    {
        $this->elo = $elo;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return Kingdom
     */
    public function setDefaultElo()
    {
        if (null === $this->getElo()) {
            $this->setElo(self::DEFAULT_ELO);
        }

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Kingdom
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
     * @ORM\PrePersist()
     *
     * @return Kingdom
     */
    public function setDefaultNetWorth()
    {
        if (null === $this->getNetWorth()) {
            $this->setNetWorth(0);
        }

        return $this;
    }

    /**
     * Set netWorth
     *
     * @param integer $netWorth
     *
     * @return Kingdom
     */
    public function setNetWorth($netWorth)
    {
        $this->netWorth = $netWorth;

        return $this;
    }

    /**
     * Get netWorth
     *
     * @return integer
     */
    public function getNetWorth()
    {
        return $this->netWorth;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return Kingdom
     */
    public function setDefaultLiquidity()
    {
        if (null === $this->getLiquidity()) {
            $this->setLiquidity(0);
        }

        return $this;
    }

    /**
     * Set liquidity
     *
     * @param integer $liquidity
     *
     * @return Kingdom
     */
    public function setLiquidity($liquidity)
    {
        $this->liquidity = $liquidity;

        return $this;
    }

    /**
     * Get liquidity
     *
     * @return integer
     */
    public function getLiquidity()
    {
        return $this->liquidity;
    }

    /**
     * Set attack
     *
     * @param integer $attack
     *
     * @return Kingdom
     */
    public function setAttack($attack)
    {
        $this->attack = $attack;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return Kingdom
     */
    public function setDefaultAttack()
    {
        if (null === $this->getAttack()) {
            $this->setAttack(0);
        }

        return $this;
    }

    /**
     * Get attack
     *
     * @return integer
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * Set defense
     *
     * @param integer $defense
     *
     * @return Kingdom
     */
    public function setDefense($defense)
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     *
     * @return Kingdom
     */
    public function setDefaultDefense()
    {
        if (null === $this->getDefense()) {
            $this->setDefense(0);
        }

        return $this;
    }

    /**
     * Get defense
     *
     * @return integer
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * Set world
     *
     * @param World $world
     *
     * @return Kingdom
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
     * Set user
     *
     * @param User $user
     *
     * @return Kingdom
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add resource
     *
     * @param KingdomResource $resource
     *
     * @return Kingdom
     */
    public function addResource(KingdomResource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param KingdomResource $resource
     */
    public function removeResource(KingdomResource $resource)
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
     * @param Resource $resource
     * @return null|KingdomResource
     */
    public function getResource(Resource $resource)
    {
        foreach ($this->getResources() as $kingdomResource) {
            if ($resource === $kingdomResource->getResource()) {
                return $kingdomResource;
            }
        }

        return null;
    }

    /**
     * Add queue
     *
     * @param Queue $queue
     *
     * @return Kingdom
     */
    public function addQueue(Queue $queue)
    {
        $this->queues[] = $queue;

        return $this;
    }

    /**
     * Remove queue
     *
     * @param Queue $queue
     */
    public function removeQueue(Queue $queue)
    {
        $this->queues->removeElement($queue);
    }

    /**
     * Get queues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * Add policy
     *
     * @param PolicyInstance $policy
     *
     * @return Kingdom
     */
    public function addPolicy(PolicyInstance $policy)
    {
        $this->policies[] = $policy;

        return $this;
    }

    /**
     * Remove policy
     *
     * @param PolicyInstance $policy
     */
    public function removePolicy(PolicyInstance $policy)
    {
        $this->policies->removeElement($policy);
    }

    /**
     * Get policies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPolicies()
    {
        return $this->policies;
    }

    /**
     * @return PolicyInstance|null
     */
    public function getActivePolicy()
    {
        if (!count($this->getPolicies())) {
            return null;
        }

        /** @var PolicyInstance */
        $activePolicy = $this->getPolicies()->first();
        $worldTick = $this->getWorld()->getTick();
        $policyEndTick = $activePolicy->getStartTick() + $activePolicy->getTickDuration();
        if ($worldTick >= $activePolicy->getStartTick() && $worldTick < $policyEndTick) {
            return $activePolicy;
        }

        return null;
    }

    /**
     * @return ChatMessage
     */
    public function getLastReadChatMessage()
    {
        return $this->lastReadChatMessage;
    }

    /**
     * @param ChatMessage $lastReadChatMessage
     * @return Kingdom
     */
    public function setLastReadChatMessage(ChatMessage $lastReadChatMessage)
    {
        $this->lastReadChatMessage = $lastReadChatMessage;
        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
