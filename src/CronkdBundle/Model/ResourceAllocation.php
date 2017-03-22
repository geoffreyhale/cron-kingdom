<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Resource;

class ResourceAllocation
{
    /** @var  Resource */
    private $resource;

    /** @var  int */
    private $quantity;

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param Resource $resource
     */
    public function setResource(Resource $resource)
    {
        $this->resource= $resource;
    }

    /**
     * @return array
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }
}