<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Policy\KingdomPolicy;
use CronkdBundle\Entity\Policy\KingdomPolicyInstance;
use CronkdBundle\Entity\Policy\KingdomPolicyResource;
use CronkdBundle\Entity\Policy\WorldPolicy;
use CronkdBundle\Entity\Policy\WorldPolicyInstance;
use CronkdBundle\Entity\Policy\WorldPolicyResource;
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
     * @param KingdomPolicyInstance $policyInstance
     * @param Kingdom $kingdom
     * @return KingdomPolicyInstance
     */
    public function create(KingdomPolicyInstance $policyInstance, Kingdom $kingdom)
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
     * @param KingdomPolicy $policy
     * @param Resource $resource
     * @return KingdomPolicyResource|null
     */
    private function getResourcePolicy($policy, Resource $resource)
    {
        if (null === $resource) {
            return null;
        }

        // @todo make interface instead
        if (!$policy instanceof KingdomPolicy && !$policy instanceof WorldPolicy) {
            return null;
        }

        $resources = [];
        if ($policy instanceof KingdomPolicy) {
            $resources = $policy->getResources();
        } elseif ($policy instanceof WorldPolicy) {
            $resources = $policy->getResultingResources();
        }

        foreach ($resources as $policyResource) {
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
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $multiplier *= ($policy->getOutputMultiplier() / 100);

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $multiplier *= ($policyResource->getOutputMultiplier() / 100);
            }
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
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $multiplier *= ($policy->getAttackMultiplier() / 100);

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $multiplier *= ($policyResource->getAttackMultiplier() / 100);
            }
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
    public function calculateDefenseMultiplier(Kingdom $kingdom, Resource $resource)
    {
        $multiplier = 1;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $multiplier *= ($policy->getDefenseMultiplier() / 100);

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $multiplier *= ($policyResource->getDefenseMultiplier() / 100);
            }
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
    public function calculateCapacityMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $multiplier = 1;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $multiplier *= ($policy->getCapacityMultiplier() / 100);

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $multiplier *= ($policyResource->getCapacityMultiplier() / 100);
            }
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
    public function calculateProbePowerMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $multiplier = 1;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $multiplier *= ($policy->getProbePowerMultiplier() / 100);

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $multiplier *= ($policyResource->getProbePowerMultiplier() / 100);
            }
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
    public function calculateQueueSizeModifier(Kingdom $kingdom, Resource $resource = null)
    {
        $modifier = 0;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $modifier += $policy->getQueueSizeModifier();

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $modifier += $policyResource->getQueueSizeModifier();
            }
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
        $modifier = 0;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $modifier += $policy->getSpoilOfWarAttackCaptureMultiplier();

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $modifier += $policyResource->getSpoilOfWarAttackCaptureMultiplier();
            }
        }

        return $modifier;
    }

    /**
     * @param Kingdom $kingdom
     * @param Resource $resource
     * @return float
     */
    public function calculateDefenderSpoilOfWarPercentageMultiplier(Kingdom $kingdom, Resource $resource = null)
    {
        $modifier = 0;
        $policies = [];
        $activePolicy = $kingdom->getActivePolicy();
        if (null !== $activePolicy) {
            $policies[] = $activePolicy;
        }
        $activePolicies = $this->em->getRepository(WorldPolicyInstance::class)->findActivePolicies($kingdom);
        foreach ($activePolicies as $activePolicy) {
            $policies[] = $activePolicy;
        }

        foreach ($policies as $policy) {
            $policy = $policy->getPolicy();
            $modifier += $policy->getSpoilOfWarDefenseCaptureMultiplier();

            $policyResource = $this->getResourcePolicy($policy, $resource);
            if (null !== $policyResource) {
                $modifier += $policyResource->getSpoilOfWarDefenseCaptureMultiplier();
            }
        }

        return $modifier;
    }
}