<?php

namespace CronkdBundle\Entity;

/**
 * Technology
 */
class Technology
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $levels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->levels = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Technology
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add level
     *
     * @param \CronkdBundle\Entity\KingdomResource $level
     *
     * @return Technology
     */
    public function addLevel(\CronkdBundle\Entity\KingdomResource $level)
    {
        $this->levels[] = $level;

        return $this;
    }

    /**
     * Remove level
     *
     * @param \CronkdBundle\Entity\KingdomResource $level
     */
    public function removeLevel(\CronkdBundle\Entity\KingdomResource $level)
    {
        $this->levels->removeElement($level);
    }

    /**
     * Get levels
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLevels()
    {
        return $this->levels;
    }
}
