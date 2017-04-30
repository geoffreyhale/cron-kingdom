<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomPolicy;

class KingdomState
{
    /** @var  Kingdom */
    private $kingdom;
    /** @var array  */
    private $currentQueues = [];
    /** @var int  */
    private $numWins = 0;
    /** @var int */
    private $numLosses = 0;
    /** @var int  */
    private $notificationCount = 0;
    /** @var bool  */
    private $availableAttack = false;

    public function __construct(Kingdom $kingdom)
    {
        $this->kingdom = $kingdom;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->kingdom->getResources();
    }

    /**
     * @param array $currentQueues
     * @return self
     */
    public function setCurrentQueues(array $currentQueues)
    {
        $this->currentQueues = $currentQueues;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurrentQueues()
    {
        return $this->currentQueues;
    }

    /**
     * @return KingdomPolicy|null
     */
    public function getActivePolicy()
    {
        return $this->kingdom->getActivePolicy();
    }

    /**
     * @return string
     */
    public function getActivePolicyName()
    {
        return null === $this->kingdom->getActivePolicy() ? 'None' : $this->kingdom->getActivePolicy()->getPolicy()->getName();
    }

    /**
     * @return string
     */
    public function getActivePolicyEndDiff()
    {
        if (null !== $this->kingdom->getActivePolicy()) {
            return $this->kingdom->getActivePolicy()->getEndTime()->diff(new \DateTime())->format('%h:%I');
        }

        return '';
    }

    /**
     * @param $wins
     * @param $losses
     * @return $this
     */
    public function setWinLossRecord($wins, $losses)
    {
        $this->numWins   = $wins;
        $this->numLosses = $losses;

        return $this;
    }

    public function getNumWins()
    {
        return $this->numWins;
    }

    public function getNumLosses()
    {
        return $this->numLosses;
    }

    /**
     * @return string
     */
    public function getWinLossRecord()
    {
        return $this->numWins . '-' . $this->numLosses;
    }

    public function setNotificationCount(int $count)
    {
        $this->notificationCount = $count;

        return $this;
    }

    public function getNotificationCount()
    {
        return $this->notificationCount;
    }

    /**
     * @param bool $availableAttack
     * @return self
     */
    public function setAvailableAttack(bool $availableAttack)
    {
        $this->availableAttack = $availableAttack;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAvailableAttack()
    {
        return $this->availableAttack;
    }
}