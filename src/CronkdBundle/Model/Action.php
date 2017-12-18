<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;

class Action
{
    /** @var  Kingdom */
    private $kingdom;
    /** @var  int */
    private $quantity;

    /**
     * @return Kingdom
     */
    public function getKingdom()
    {
        return $this->kingdom;
    }

    /**
     * @param Kingdom $kingdom
     */
    public function setKingdom(Kingdom $kingdom)
    {
        $this->kingdom = $kingdom;
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