<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;

class AttackPlan
{
    /** @var  Kingdom */
    private $target;

    /** @var  int */
    private $militaryAllocations;

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
    public function getMilitaryAllocations()
    {
        return $this->militaryAllocations;
    }

    /**
     * @param int $militaryAllocations
     */
    public function setMilitaryAllocations(int $militaryAllocations)
    {
        $this->militaryAllocations = $militaryAllocations;
    }
}