<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomPolicy;
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
     * @param KingdomPolicy $kingdomPolicy
     * @param Kingdom $kingdom
     * @return KingdomPolicy
     */
    public function create(KingdomPolicy $kingdomPolicy, Kingdom $kingdom)
    {
        $kingdomPolicy->setKingdom($kingdom);
        $kingdomPolicy->setStartTime(new \DateTime());
        $kingdomPolicy->setEndTime((new \DateTime)->add(new \DateInterval('P1D')));
        $this->em->persist($kingdomPolicy);
        $this->em->flush();

        return $kingdomPolicy;
    }
}