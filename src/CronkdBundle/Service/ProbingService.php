<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\PolicyInstance;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Event\ProbeEvent;
use CronkdBundle\Manager\LumberMill;
use CronkdBundle\Manager\PolicyManager;
use CronkdBundle\Model\ProbeReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProbingService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var PolicyManager  */
    private $policyManager;
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;
    /** @var LumberMill  */
    private $logManager;

    public function __construct(
        EntityManagerInterface $em,
        PolicyManager $policyManager,
        EventDispatcherInterface $dispatcher,
        LumberMill $logManager
    ) {
        $this->em              = $em;
        $this->policyManager   = $policyManager;
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
        $outcome = $this->calculateProbeAttemptOutcome($quantity);
        if ($outcome) {
            $probedResources = $this->em->getRepository(KingdomResource::class)
                ->findResourcesThatMayBeProbed($target);

            $policy = $target->getActivePolicy();
            $report->setResult(true);
            $report->setData([
                'Resources' => $probedResources,
                'Policy'    => (null !== $policy ? $policy->getPolicy()->getName() : null),
                'Kingdom'   => $target->getName(),
            ]);
        }

        $probeEventForProber = $this->logManager->logProbeResult($kingdom, $target, $report);
        $report->setProbeEvent($probeEventForProber);

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