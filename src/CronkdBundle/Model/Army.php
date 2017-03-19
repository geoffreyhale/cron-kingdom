<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;

class Army
{
    /** @var array  */
    private $army = [];
    /** @var  Kingdom */
    private $kingdom;

    public function __construct(Kingdom $kingdom)
    {
        $this->kingdom = $kingdom;
    }

    /**
     * @param Resource $resource
     * @param int $quantity
     * @return Army
     */
    public function addResource(Resource $resource, int $quantity)
    {
        $this->initializeResource($resource);
        $this->army[$resource->getName()]->quantity += $quantity;

        return $this;
    }

    /**
     * @param Resource $resource
     * @param int $quantity
     * @return Army
     */
    public function removeResource(Resource $resource, int $quantity)
    {
        $this->initializeResource($resource);
        $this->army[$resource->getName()]->quantity -= $quantity;
        if (0 > $this->army[$resource->getName()]) {
            $this->army[$resource->getName()] = 0;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getArmyValue()
    {
        $value = 0;

        /** @var ArmyResource $armyResource */
        foreach ($this->army as $armyResource) {
            $value += $armyResource->quantity * $armyResource->resource->getValue();
        }

        return $value;
    }

    /**
     * @return Kingdom
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * @return bool
     */
    public function containsResources()
    {
        foreach ($this->army as $armyResource) {
            if (0 < $armyResource->quantity) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAllTypesOfUnits()
    {
        $resourcesNames = [];

        foreach ($this->army as $resourceName => $armyResource) {
            $resourcesNames[] = $resourceName;
        }

        return $resourcesNames;
    }

    /**
     * @param string $resourceName
     * @return int
     */
    public function getQuantityOfUnit(string $resourceName)
    {
        if (!isset($this->army[$resourceName])) {
            return 0;
        }

        return $this->army[$resourceName]->quantity;
    }

    /**
     * @param KingdomResource $kingdomResource
     * @return bool
     */
    public function hasEnoughToSend(KingdomResource $kingdomResource)
    {
        $resourceName = $kingdomResource->getResource()->getName();
        if (!isset($this->army[$resourceName])) {
            return true;
        }

        return $kingdomResource->getQuantity() >= $this->army[$resourceName]->quantity;
    }

    /**
     * @param Army $army
     * @return int
     */
    public function compare(Army $army)
    {
        $netWorth = $this->getArmyValue();
        $otherNetWorth = $army->getArmyValue();

        if ($netWorth > $otherNetWorth) {
            return 1;
        } elseif ($otherNetWorth > $netWorth) {
            return -1;
        }

        return 0;
    }

    /**
     * @param Resource $resource
     */
    private function initializeResource(Resource $resource)
    {
        $resourceName = $resource->getName();
        if (!isset($this->army[$resourceName])) {
            $this->army[$resourceName] = new ArmyResource($resource, 0);
        }
    }
}