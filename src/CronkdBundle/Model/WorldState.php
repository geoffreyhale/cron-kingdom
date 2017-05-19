<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\World;

class WorldState
{
    /** @var World  */
    private $world;
    /** @var array  */
    private $policies;
    /** @var int  */
    private $aggregateNetWorth = 0;
    /** @var array  */
    private $kingdomsByElo = [];
    /** @var array  */
    private $kingdomsByNetWorth = [];
    /** @var array  */
    private $kingdomsByWinLossRecord = [];

    public function __construct(World $world, array $policies)
    {
        $this->world    = $world;
        $this->policies = $policies;
    }

    /**
     * @return bool
     */
    public function hasPolicies()
    {
        return count($this->policies) ? true : false;
    }

    /**
     * @param int $netWorth
     * @return self
     */
    public function setAggregateNetWorth(int $netWorth)
    {
        $this->aggregateNetWorth = $netWorth;

        return $this;
    }

    /**
     * @return int
     */
    public function getAggregateNetWorth()
    {
        return $this->aggregateNetWorth;
    }


    /**
     * @param array $kingdomsByElo
     * @return $this
     */
    public function setKingdomsByElo(array $kingdomsByElo)
    {
        $this->kingdomsByElo = $kingdomsByElo;

        return $this;
    }

    /**
     * @return array
     */
    public function getKingdomsByElo()
    {
        return $this->kingdomsByElo;
    }

    /**
     * @param array $kingdoms
     * @return self
     */
    public function setKingdomsByNetWorth(array $kingdoms)
    {
        $this->kingdomsByNetWorth = $kingdoms;

        return $this;
    }

    /**
     * @return array
     */
    public function getKingdomsByNetWorth()
    {
        return $this->kingdomsByNetWorth;
    }

    /**
     * @param array $kingdomsByWinLoss
     * @return $this
     */
    public function setKingdomsByWinLossRecord(array $kingdomsByWinLoss)
    {
        $this->kingdomsByWinLossRecord = $kingdomsByWinLoss;

        return $this;
    }
    /**
     * @return array
     */
    public function getKingdomsByWinLossRecord()
    {
        return $this->kingdomsByWinLossRecord;
    }
}