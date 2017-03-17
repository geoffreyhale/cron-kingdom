<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;

class ProbingService
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Kingdom $target
     * @param $quantity
     * @return ProbeReport
     */
    public function probe(Kingdom $target, $quantity)
    {
        $report = new ProbeReport();

        if ($this->calculateProbeAttemptOutcome($quantity)) {
            $availableResources = $this->em->getRepository(KingdomResource::class)
                ->findResourcesThatMayBeProbed($target);

            $report->setResult(true);
            $report->setData([$availableResources[random_int(0, count($availableResources)-1)]]);
        }

        return $report;
    }

    /**
     * @param $quantity
     * @return bool
     */
    private function calculateProbeAttemptOutcome($quantity)
    {
        $successful = (int) number_format(100 * (1 - (1 / $quantity)), 0);
        $actual = random_int(0, 100);
        if ($successful > $actual) {
            return true;
        }

        return false;
    }
}