<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;

class ProbingService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var LogManager  */
    private $logManager;

    public function __construct(EntityManagerInterface $em, LogManager $logManager)
    {
        $this->em         = $em;
        $this->logManager = $logManager;
    }

    /**
     * @param Kingdom $kingdom
     * @param Kingdom $target
     * @param $quantity
     * @return ProbeReport
     */
    public function probe(Kingdom $kingdom, Kingdom $target, $quantity)
    {
        $report = new ProbeReport();

        if ($this->calculateProbeAttemptOutcome($quantity)) {
            $availableResources = $this->em->getRepository(KingdomResource::class)
                ->findResourcesThatMayBeProbed($target);

            $report->setResult(true);
            $report->setData([$availableResources[random_int(0, count($availableResources)-1)]]);
        }

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_PROBE,
            ($report->getResult() ? 'Successful' : 'Failed') . ' probe attempt against ' . $target->getName()
        );
        $this->logManager->createLog(
            $target,
            Log::TYPE_PROBE,
            ($report->getResult() ? 'Successful' : 'Failed') . ' probe attempt from ' . $kingdom->getName()
        );

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