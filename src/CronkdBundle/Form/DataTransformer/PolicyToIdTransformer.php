<?php
namespace CronkdBundle\Form\DataTransformer;

use CronkdBundle\Entity\Policy\KingdomPolicy;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PolicyToIdTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param KingdomPolicy|null $policy
     * @return string
     */
    public function transform($policy)
    {
        if (null === $policy) {
            return '';
        }

        return $policy->getId();
    }

    /**
     * @param int $policyId
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($policyId)
    {
        $policy = $this->manager
            ->getRepository(KingdomPolicy::class)
            ->find($policyId)
        ;

        if (null === $policy) {
            throw new TransformationFailedException(sprintf(
                'A policy with id "%s" does not exist!',
                $policyId
            ));
        }

        return $policy;
    }
}