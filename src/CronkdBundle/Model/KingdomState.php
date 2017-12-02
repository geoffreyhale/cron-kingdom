<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\PolicyInstance;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Exceptions\KingdomDoesNotHaveResourceException;

class KingdomState
{
    /** @var  Kingdom */
    private $kingdom;
    /** @var array  */
    private $currentQueues = [];
    /** @var int  */
    private $numWins = 0;
    /** @var int */
    private $numLosses = 0;
    /** @var int  */
    private $notificationCount = 0;
    /** @var bool  */
    private $availableAttack = false;

    public function __construct(Kingdom $kingdom)
    {
        $this->kingdom = $kingdom;
    }

    /**
     * @return Kingdom
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->kingdom->getResources();
    }

    /**
     * @param string $resourceName
     * @return int
     */
    public function getAvailableResourceQuantity(string $resourceName)
    {
        foreach ($this->kingdom->getResources() as $kingdomResource) {
            if ($resourceName == $kingdomResource->getResource()->getName()) {
                return $kingdomResource->getQuantity();
            }
        }

        return 0;
    }

    /**
     * @param string $resourceName
     * @return bool
     */
    public function hasAvailableResource(string $resourceName)
    {
        foreach ($this->kingdom->getResources() as $kingdomResource) {
            if ($resourceName == $kingdomResource->getResource()->getName()) {
                return $kingdomResource->getQuantity() > 0;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasAvailableProbingResources()
    {
        foreach ($this->kingdom->getResources() as $kingdomResource) {
            if ($kingdomResource->getQuantity() > 0 && $kingdomResource->getResource()->getProbePower() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasAvailableAttackingResources()
    {
        foreach ($this->kingdom->getResources() as $kingdomResource) {
            if ($kingdomResource->getQuantity() > 0 && $kingdomResource->getResource()->getAttack() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Resource $resource
     * @return bool
     */
    public function canPerformActionOnResource(Resource $resource)
    {
        if (!$resource->getCanBeProduced()) {
            return false;
        }

        $kingdomResource = $this->kingdom->getResource($resource);
        if (null === $kingdomResource) {
            return false;
        }

        $action = $kingdomResource->getResource()->getActions()->first();
        if (null === $action) {
            return false;
        }

        foreach ($action->getInputs() as $resourceActionInput) {
            $kingdomResource = $this->getKingdomResource($resourceActionInput->getResource()->getName());
            if ($kingdomResource->getQuantity() < $resourceActionInput->getInputQuantity()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $resourceName
     * @return KingdomResource
     * @throws KingdomDoesNotHaveResourceException
     */
    private function getKingdomResource(string $resourceName)
    {
        foreach ($this->kingdom->getResources() as $kingdomResource) {
            if ($kingdomResource->getResource()->getName() == $resourceName) {
                return $kingdomResource;
            }
        }

        throw new KingdomDoesNotHaveResourceException($resourceName);
    }


    /**
     * @param bool $availableAttack
     * @return self
     */
    public function setAvailableAttack(bool $availableAttack)
    {
        $this->availableAttack = $availableAttack;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAvailableAttack()
    {
        return $this->availableAttack && $this->hasAvailableAttackingResources();
    }

    /**
     * @param array $currentQueues
     * @return self
     */
    public function setCurrentQueues(array $currentQueues)
    {
        $this->currentQueues = $currentQueues;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurrentQueues()
    {
        return $this->currentQueues;
    }

    /**
     * @return PolicyInstance|null
     */
    public function getActivePolicy()
    {
        return $this->kingdom->getActivePolicy();
    }

    /**
     * @return string
     */
    public function getActivePolicyName()
    {
        return null === $this->kingdom->getActivePolicy() ? 'None' : $this->kingdom->getActivePolicy()->getPolicy()->getName();
    }

    /**
     * @return string
     */
    public function getActivePolicyEndDiff()
    {
        $activePolicy = $this->kingdom->getActivePolicy();
        $world = $this->kingdom->getWorld();
        if (null !== $activePolicy) {
            $endTick = $activePolicy->getStartTick() + $activePolicy->getTickDuration();
            $ticksLeft = $endTick - $this->kingdom->getWorld()->getTick();
            $minutesToEndTick = (60 * $ticksLeft) - (new \DateTime())->format('i');

            return (new \DateTime())
                ->add(new \DateInterval('PT'.$minutesToEndTick.'M'))
                ->diff(new \DateTime())
                ->format('%dd, %hh, %im')
            ;
        }

        return '';
    }

    /**
     * @param $wins
     * @param $losses
     * @return $this
     */
    public function setWinLossRecord($wins, $losses)
    {
        $this->numWins   = $wins;
        $this->numLosses = $losses;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumWins()
    {
        return $this->numWins;
    }

    /**
     * @return int
     */
    public function getNumLosses()
    {
        return $this->numLosses;
    }

    /**
     * @return string
     */
    public function getWinLossRecord()
    {
        return $this->numWins . '-' . $this->numLosses;
    }

    /**
     * @param int $count
     * @return self
     */
    public function setNotificationCount(int $count)
    {
        $this->notificationCount = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getNotificationCount()
    {
        return $this->notificationCount;
    }

    /**
     * @return int
     */
    public function getModifiedAttackPower()
    {
        $attack = $this->kingdom->getAttack();

        $activePolicy = $this->kingdom->getActivePolicy();
        if ($activePolicy) {
            $attackMultiplier = ($activePolicy->getPolicy()->getAttackMultiplier() / 100);
            $attack *= $attackMultiplier;
        }

        return floor($attack);
    }

    /**
     * @return int
     */
    public function getModifiedDefensePower()
    {
        $defense = $this->kingdom->getDefense();

        $activePolicy = $this->kingdom->getActivePolicy();
        if ($activePolicy) {
            $defenseMultiplier = ($activePolicy->getPolicy()->getDefenseMultiplier() / 100);
            $defense *= $defenseMultiplier;
        }

        return floor($defense);
    }
}