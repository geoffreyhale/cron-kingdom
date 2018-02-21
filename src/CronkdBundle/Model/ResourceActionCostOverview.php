<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Resource\Resource;

class ResourceActionCostOverview
{
    /** @var array  */
    private $costs = [];

    /**
     * @param Resource $resource
     * @param int $cost
     * @param int $maxCost
     * @param int $maxQuantity
     * @param string $strategy
     * @return $this
     */
    public function addInput(Resource $resource, int $cost, int $maxCost, int $maxQuantity, string $strategy)
    {
        $this->costs[$resource->getName()] = [
            'cost'        => $cost,
            'maxCost'     => $maxCost,
            'maxQuantity' => $maxQuantity,
            'strategy'    => $strategy,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getOverview()
    {
        return $this->costs;
    }

    /**
     * @return int
     */
    public function getMaxQuantityToProduce()
    {
        $maxQuantity = 10E99;
        foreach ($this->costs as $cost) {
            if ($maxQuantity > $cost['maxQuantity']) {
                $maxQuantity = $cost['maxQuantity'];
            }
        }

        if ($maxQuantity < 0) {
            $maxQuantity = 0;
        }

        return $maxQuantity;
    }
}