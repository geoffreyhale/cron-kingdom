<?php
namespace CronkdBundle\Entity\Policy;

trait PolicyTrait
{
    /**
     * Output modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="output_multiplier", type="float")
     */
    private $outputMultiplier = 100;

    /**
     * @var float
     *
     * @ORM\Column(name="net_worth_multiplier", type="float")
     */
    private $netWorthMultiplier = 100;

    /**
     * Attack power modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="attack_multiplier", type="float")
     */
    private $attackMultiplier = 100;

    /**
     * Defense power modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="defense_multiplier", type="float")
     */
    private $defenseMultiplier = 100;

    /**
     * Probe power modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="probe_power_multiplier", type="float")
     */
    private $probePowerMultiplier = 100;

    /**
     * Capacity modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="capacity_multiplier", type="float")
     */
    private $capacityMultiplier = 100;

    /**
     * Queue size modifier as an integer.
     *
     * @var int
     *
     * @ORM\Column(name="queue_size_modifier", type="integer")
     */
    private $queueSizeModifier = 0;

    /**
     * Attacking spoil of war modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="spoil_of_war_attack_capture_multiplier", type="float")
     */
    private $spoilOfWarAttackCaptureMultiplier = 100;

    /**
     * Defending spoil of war modifier as a multiplier.
     *
     * @var float
     *
     * @ORM\Column(name="spoil_of_war_defense_capture_multiplier", type="float")
     */
    private $spoilOfWarDefenseCaptureMultiplier = 100;

    /**
     * Set outputMultiplier
     *
     * @param float $outputMultiplier
     *
     * @return Policy
     */
    public function setOutputMultiplier($outputMultiplier)
    {
        $this->outputMultiplier = $outputMultiplier;

        return $this;
    }

    /**
     * Get outputMultiplier
     *
     * @return float
     */
    public function getOutputMultiplier()
    {
        return $this->outputMultiplier;
    }

    /**
     * Set attackMultiplier
     *
     * @param float $attackMultiplier
     *
     * @return Policy
     */
    public function setAttackMultiplier($attackMultiplier)
    {
        $this->attackMultiplier = $attackMultiplier;

        return $this;
    }

    /**
     * Get attackMultiplier
     *
     * @return float
     */
    public function getAttackMultiplier()
    {
        return $this->attackMultiplier;
    }

    /**
     * Set defenseMultiplier
     *
     * @param float $defenseMultiplier
     *
     * @return Policy
     */
    public function setDefenseMultiplier($defenseMultiplier)
    {
        $this->defenseMultiplier = $defenseMultiplier;

        return $this;
    }

    /**
     * Get defenseMultiplier
     *
     * @return float
     */
    public function getDefenseMultiplier()
    {
        return $this->defenseMultiplier;
    }

    /**
     * Set probePowerMultiplier
     *
     * @param float $probePowerMultiplier
     *
     * @return Policy
     */
    public function setProbePowerMultiplier($probePowerMultiplier)
    {
        $this->probePowerMultiplier = $probePowerMultiplier;

        return $this;
    }

    /**
     * Get probePowerMultiplier
     *
     * @return float
     */
    public function getProbePowerMultiplier()
    {
        return $this->probePowerMultiplier;
    }

    /**
     * Set capacityMultiplier
     *
     * @param float $capacityMultiplier
     *
     * @return Policy
     */
    public function setCapacityMultiplier($capacityMultiplier)
    {
        $this->capacityMultiplier = $capacityMultiplier;

        return $this;
    }

    /**
     * Get capacityMultiplier
     *
     * @return float
     */
    public function getCapacityMultiplier()
    {
        return $this->capacityMultiplier;
    }

    /**
     * Set queueSizeMultiplier
     *
     * @param integer $queueSizeModifier
     *
     * @return Policy
     */
    public function setQueueSizeModifier($queueSizeModifier)
    {
        $this->queueSizeModifier = $queueSizeModifier;

        return $this;
    }

    /**
     * Get queueSizeMultiplier
     *
     * @return integer
     */
    public function getQueueSizeModifier()
    {
        return $this->queueSizeModifier;
    }

    /**
     * Set spoilOfWarAttackCaptureMultiplier
     *
     * @param float $spoilOfWarAttackCaptureMultiplier
     *
     * @return Policy
     */
    public function setSpoilOfWarAttackCaptureMultiplier($spoilOfWarAttackCaptureMultiplier)
    {
        $this->spoilOfWarAttackCaptureMultiplier = $spoilOfWarAttackCaptureMultiplier;

        return $this;
    }

    /**
     * Get spoilOfWarAttackCaptureMultiplier
     *
     * @return float
     */
    public function getSpoilOfWarAttackCaptureMultiplier()
    {
        return $this->spoilOfWarAttackCaptureMultiplier;
    }

    /**
     * Set spoilOfWarDefenseCaptureMultiplier
     *
     * @param float $spoilOfWarDefenseCaptureMultiplier
     *
     * @return Policy
     */
    public function setSpoilOfWarDefenseCaptureMultiplier($spoilOfWarDefenseCaptureMultiplier)
    {
        $this->spoilOfWarDefenseCaptureMultiplier = $spoilOfWarDefenseCaptureMultiplier;

        return $this;
    }

    /**
     * Get spoilOfWarDefenseCaptureMultiplier
     *
     * @return float
     */
    public function getSpoilOfWarDefenseCaptureMultiplier()
    {
        return $this->spoilOfWarDefenseCaptureMultiplier;
    }

    /**
     * @return bool
     */
    public function hasAttributes()
    {
        return !empty($this->getOutputMultiplier()) ||
            !empty($this->getAttackMultiplier()) ||
            !empty($this->getDefenseMultiplier()) ||
            !empty($this->getProbePowerMultiplier()) ||
            !empty($this->getCapacityMultiplier()) ||
            !empty($this->getQueueSizeModifier()) ||
            !empty($this->getSpoilOfWarAttackCaptureMultiplier()) ||
            !empty($this->getSpoilOfWarDefenseCaptureMultiplier())
        ;
    }
}