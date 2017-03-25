<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Event\ProbeEvent;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProbingService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var LogManager  */
    private $logManager;

    public function __construct(
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher,
        LogManager $logManager
    ) {
        $this->em              = $em;
        $this->eventDispatcher = $dispatcher;
        $this->logManager      = $logManager;
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
            $report->setData($availableResources);
        }

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_PROBE,
            ($report->getResult() ? 'Successful' : 'Failed') . ' hacking attempt against ' . $target->getName()
        );
        $this->logManager->createLog(
            $target,
            Log::TYPE_PROBE,
            ($report->getResult() ? 'Successful hacking attempt from another kingdom' : 'Failed hacking attempt from ' . $kingdom->getName()),
            true
        );

        $event = new ProbeEvent($kingdom);
        $this->eventDispatcher->dispatch('event.probe', $event);

        return $report;
    }

    /**
     * @param $quantity
     * @return bool
     */
    private function calculateProbeAttemptOutcome($quantity)
    {
        $successChance = (int) number_format(100 * (1 - (1 / pow(2, $quantity))), 0);
        $actual = random_int(0, 100);

        if ($successChance > $actual) {
            return true;
        }

        return false;
    }
}