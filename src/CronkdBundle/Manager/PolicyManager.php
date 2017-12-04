<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Policy\Policy;
use CronkdBundle\Entity\Policy\PolicyInstance;
use CronkdBundle\Entity\Policy\PolicyResource;
use CronkdBundle\Entity\Resource\Resource;
use Doctrine\ORM\EntityManagerInterface;

class PolicyManager
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param PolicyInstance $policyInstance
     * @param Kingdom $kingdom
     * @return PolicyInstance
     */
    public function create(PolicyInstance $policyInstance, Kingdom $kingdom)
    {
        if (null !== $kingdom->getActivePolicy()) {
            return $kingdom->getActivePolicy();
        }

        $world = $kingdom->getWorld();
        $policyInstance->setKingdom($kingdom);
        $policyInstance->setTickDuration($world->getPolicyDuration());
        $policyInstance->setStartTick($world->getTick());
        $this->em->persist($policyInstance);
        $this->em->flush();

        return $policyInstance;
    }

    /**
     * @param Policy $policy
     * @param Resource $resource
     * @return PolicyResource|null
     */
    private function getResourcePolicy(Policy $policy, Resource $resource)
    {
        if (null === $resource) {
            return null;
        }

        foreach ($policy->getResources() as $policyResource) {
            if ($policyResource->getResource() == $resource) {
                return $policyResource;
            }
        }

        return null;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateOutputMultiplier(Kingdom $kingdom, Resource $resource)
    {
        $multiplier = 1;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $multiplier;
        }

        $policy = $activePolicy->getPolicy();
        $multiplier *= ($policy->getOutputMultiplier() / 100);

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $multiplier = ($policyResource->getOutputMultiplier() / 100);
        }

        if ($multiplier < 0) {
            $multiplier = 0;
        }

        return $multiplier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateAttackMultiplier(Kingdom $kingdom, Resource $resource)
    {
        $multiplier = 1;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $multiplier;
        }

        $policy = $activePolicy->getPolicy();
        $multiplier *= ($policy->getAttackMultiplier() / 100);

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $multiplier *= ($policyResource->getAttackMultiplier() / 100);
        }

        return $multiplier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateDefenseMultiplier(Kingdom $kingdom, Resource $resource)
    {
        $multiplier = 1;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $multiplier;
        }

        $policy = $activePolicy->getPolicy();
        $multiplier *= ($policy->getDefenseMultiplier() / 100);

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $multiplier *= ($policyResource->getDefenseMultiplier() / 100);
        }

        return $multiplier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateCapacityMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $multiplier = 1;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $multiplier;
        }

        $policy = $activePolicy->getPolicy();
        $multiplier *= ($policy->getCapacityMultiplier() / 100);

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $multiplier *= ($policyResource->getCapacityMultiplier() / 100);
        }

        return $multiplier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateProbePowerMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $multiplier = 1;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $multiplier;
        }

        $policy = $activePolicy->getPolicy();
        $multiplier *= ($policy->getProbePowerMultiplier() / 100);

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $multiplier *= ($policyResource->getProbePowerMultiplier() / 100);
        }

        return $multiplier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateQueueSizeModifier(Kingdom $kingdom, Resource $resource = null)
    {
        $modifier = 0;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $modifier;
        }

        $policy = $activePolicy->getPolicy();
        $modifier += $policy->getQueueSizeModifier();

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $modifier += $policyResource->getQueueSizeModifier();
        }

        return $modifier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateAttackerSpoilOfWarPercentageMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $percentage = 0;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $percentage;
        }
        if (null !== $resource && !$resource->getSpoilOfWar()) {
            return $percentage;
        }

        $policy = $activePolicy->getPolicy();
        $percentage += $policy->getSpoilOfWarAttackCaptureMultiplier();

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $percentage += $policyResource->getSpoilOfWarAttackCaptureMultiplier();
        }

        return $percentage;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateDefenderSpoilOfWarPercentageMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $percentage = 0;
        $activePolicy = $kingdom->getActivePolicy();
        if (null === $activePolicy) {
            return $percentage;
        }
        if (null !== $resource && !$resource->getSpoilOfWar()) {
            return $percentage;
        }

        $policy = $activePolicy->getPolicy();
        $percentage += $policy->getSpoilOfWarDefenseCaptureMultiplier();

        $policyResource = $this->getResourcePolicy($policy, $resource);
        if (null !== $policyResource) {
            $percentage += $policyResource->getSpoilOfWarDefenseCaptureMultiplier();
        }

        return $percentage;
    }
}