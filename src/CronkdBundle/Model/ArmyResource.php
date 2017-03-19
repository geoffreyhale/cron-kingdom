<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Resource;

class ArmyResource
{
    /** @var  Resource */
    public $resource;
    /** @var  int */
    public $quantity;

    public function __construct(Resource $resource, int $quantity)
    {
        $this->resource = $resource;
        $this->quantity = $quantity;
    }
}