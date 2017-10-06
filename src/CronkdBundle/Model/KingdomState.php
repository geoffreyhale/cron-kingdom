<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomPolicy;
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

    public function __construct(Kingdom $kingdom, array $settings)
    {
        $this->kingdom  = $kingdom;
        $this->settings = $settings;
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
        $actionSettings = $this->settings['resources'][$resource->getName()]['action'];
        if (!$actionSettings) {
            return false;
        }

        try {
            foreach ($actionSettings['inputs'] as $inputResourceName => $inputSetting) {
                $kingdomResource = $this->getKingdomResource($inputResourceName);
                if ($kingdomResource->getQuantity() < $inputSetting['quantity']) {
                    return false;
                }
            }
        } catch (KingdomDoesNotHaveResourceException $e) {
            return false;
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
     * @return KingdomPolicy|null
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
        if (null !== $this->kingdom->getActivePolicy()) {
            return $this->kingdom->getActivePolicy()->getEndTime()->diff(new \DateTime())->format('%h:%I');
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
}