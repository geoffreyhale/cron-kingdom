<?php

namespace CronkdBundle\Entity;

/**
 * TechnologyLevel
 */
class TechnologyLevel
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $level;

    /**
     * @var integer
     */
    private $cost = 0;

    /**
     * @var \CronkdBundle\Entity\Technology
     */
    private $technology;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return TechnologyLevel
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     *
     * @return TechnologyLevel
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set technology
     *
     * @param \CronkdBundle\Entity\Technology $technology
     *
     * @return TechnologyLevel
     */
    public function setTechnology(\CronkdBundle\Entity\Technology $technology = null)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return \CronkdBundle\Entity\Technology
     */
    public function getTechnology()
    {
        return $this->technology;
    }
}
