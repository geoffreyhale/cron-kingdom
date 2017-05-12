<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;

class ProbeAttempt
{
    /** @var  Kingdom */
    private $kingdom;
    /** @var  Kingdom */
    private $target;
    /** @var  array */
    private $quantities = [];

    /**
     * @param $name
     * @param $quantity
     */
    public function __set($name, $quantity)
    {
        $this->quantities[$name] = (int) $quantity;
    }

    /**
     * @param $name
     * @return array
     */
    public function __get($name)
    {
        if (!isset($this->quantities)) {
            $this->quantities[$name] = 0;
        }

        return $this->quantities;
    }

    /**
     * @return array
     */
    public function getQuantities()
    {
        return $this->quantities;
    }

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
}