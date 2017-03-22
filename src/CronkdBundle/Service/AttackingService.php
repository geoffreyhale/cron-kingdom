<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Model\Army;
use CronkdBundle\Model\AttackReport;
use Doctrine\ORM\EntityManagerInterface;

class AttackingService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var  QueuePopulator */
    private $queuePopulator;
    /** @var  KingdomManager */
    private $kingdomManager;
    /** @var  LogManager */
    private $logManager;

    public function __construct(
        EntityManagerInterface $em,
        QueuePopulator $queuePopulator,
        KingdomManager $kingdomManager,
        LogManager $logManager
    ) {
        $this->em             = $em;
        $this->queuePopulator = $queuePopulator;
        $this->kingdomManager = $kingdomManager;
        $this->logManager     = $logManager;
    }

    /**
     * @param Kingdom $kingdom
     * @param Kingdom $target
     * @param Army $attackers
     * @return AttackReport
     */
    public function attack(Kingdom $kingdom, Kingdom $target, Army $attackers)
    {
        $defendingArmy = $this->getDefendingArmy($target);

        $result = $attackers->compare($defendingArmy);
        $report = new AttackReport($kingdom, $target, $result);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            ($report->getResult() ? 'Successful' : 'Failed') . ' attack against ' . $target->getName()
        );
        $this->logManager->createLog(
            $target,
            Log::TYPE_ATTACK,
            ($report->getResult() ? 'Failed' : 'Successful') . ' defend against ' . $kingdom->getName()
        );

        if (1 == $result) {
            $this->awardResources($report, $kingdom, $target);
        }

        foreach ($attackers->getAllTypesOfUnits() as $resourceName) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            $queue = $this->queuePopulator->build($kingdom, $resource, 24, $attackers->getQuantityOfUnit($resourceName));
            $report->addQueue($resource, $queue);
        }

        return $report;
    }

    /**
     * @param Kingdom $kingdom
     * @param array $resources
     * @return Army
     */
    public function buildArmy(Kingdom $kingdom, array $resources)
    {
        $army = new Army($kingdom);
        foreach ($resources as $resourceName => $quantity) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            $army->addResource($resource, $quantity);
        }

        return $army;
    }

    /**
     * @param Army $army
     * @return bool
     */
    public function kingdomHasResourcesToAttack(Army $army)
    {
        foreach ($army->getAllTypesOfUnits() as $resourceName) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            $kingdomResource = $this->em->getRepository(KingdomResource::class)->findOneBy([
                'kingdom'  => $army->getKingdom(),
                'resource' => $resource,
            ]);
            if (!$army->hasEnoughToSend($kingdomResource)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Kingdom $kingdom
     * @return Army
     */
    private function getDefendingArmy(Kingdom $kingdom)
    {
        $militaryResources = [
            Resource::MILITARY,
        ];

        $kingdomResources = $this->em->getRepository(KingdomResource::class)
            ->findSpecificResources($kingdom, $militaryResources);

        $resourceMap = [];
        foreach ($kingdomResources as $kingdomResource) {
            $resourceMap[$kingdomResource->getResource()->getName()] = $kingdomResource->getQuantity();
        }

        $army = $this->buildArmy($kingdom, $resourceMap);

        return $army;
    }

    /**
     * @param AttackReport $report
     * @param Kingdom $kingdom
     * @param Kingdom $target
     */
    private function awardResources(AttackReport $report, Kingdom $kingdom, Kingdom $target)
    {
        $civilianResource = $this->em->getRepository(Resource::class)->findOneByName(Resource::CIVILIAN);
        $opponentCivilians = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $target,
            'resource' => $civilianResource,
        ]);
        $civiliansToTransfer = floor($opponentCivilians->getQuantity() / 20);
        $this->kingdomManager->modifyResources($kingdom, $civilianResource, $civiliansToTransfer);
        $this->kingdomManager->modifyResources($target, $civilianResource, -1 * $civiliansToTransfer);
        $report->addModifiedResource($kingdom, $civilianResource, $civiliansToTransfer);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            'Attack awarded ' . $civiliansToTransfer . ' ' . Resource::CIVILIAN
        );
        $this->logManager->createLog(
            $target,
            Log::TYPE_ATTACK,
            'Attack lost ' . $civiliansToTransfer . ' ' . Resource::CIVILIAN
        );

        $materialResource = $this->em->getRepository(Resource::class)->findOneByName(Resource::MATERIAL);
        $opponentMaterials = $this->em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom' => $target,
            'resource' => $materialResource,
        ]);
        $materialsToTransfer = floor($opponentMaterials->getQuantity() / 10);
        $this->kingdomManager->modifyResources($kingdom, $materialResource, $materialsToTransfer);
        $this->kingdomManager->modifyResources($target, $materialResource, -1 * $materialsToTransfer);
        $report->addModifiedResource($kingdom, $materialResource, $materialsToTransfer);

        $this->logManager->createLog(
            $kingdom,
            Log::TYPE_ATTACK,
            'Attack awarded ' . $materialsToTransfer . ' ' . Resource::MATERIAL
        );
        $this->logManager->createLog(
            $target,
            Log::TYPE_ATTACK,
            'Attack lost ' . $materialsToTransfer . ' ' . Resource::MATERIAL
        );
    }
}