<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;

class ProbeAttempt
{
    /** @var  Kingdom */
    private $target;

    /** @var  int */
    private $quantity;

    /**
     * @return Kingdom
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param Kingdom $target
     */
    public function setTarget(Kingdom $target)
    {
        $this->target = $target;
    }

    /**
     * @return int
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