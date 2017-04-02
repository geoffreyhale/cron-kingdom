<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\NetWorthLog;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\EmptyGraphingDatasetException;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\ResourceManager;
use Doctrine\ORM\EntityManagerInterface;

class GraphingService
{
    /** @var EntityManagerInterface  */
    private $em;
    /** @var KingdomManager  */
    private $kingdomManager;
    /** @var ResourceManager  */
    private $resourceManager;

    public function __construct(
        EntityManagerInterface $em,
        KingdomManager $kingdomManager,
        ResourceManager $resourceManager
    ) {
        $this->em              = $em;
        $this->kingdomManager  = $kingdomManager;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @param World $world
     * @return array
     * @throws EmptyGraphingDatasetException
     */
    public function fetchNetWorthGraphData(World $world, $minTick, $maxTick)
    {
        $data = $this->em->getRepository(NetWorthLog::class)
            ->findByWorld($world, $minTick, $maxTick);
        if (empty($data)) {
            throw new EmptyGraphingDatasetException($world->getName());
        }

        $backgroundColors = [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
        ];
        $borderColors = [
            'rgba(255,99,132,1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        ];
        $players = [];
        $numPlayers = 0;

        $dataStructure = [];
        foreach ($data as $netWorthLog) {
            if (!isset($players[$netWorthLog->getKingdom()->getId()])) {
                $players[$netWorthLog->getKingdom()->getId()] = $numPlayers++;
            }

            $dataStructure['labels'][$netWorthLog->getTick()] = $netWorthLog->getTick();
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['label'] = $netWorthLog->getKingdom()->getName();
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['backgroundColor'] = $backgroundColors[$players[$netWorthLog->getKingdom()->getId()] % count($backgroundColors)];
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['borderColor'] = $borderColors[$players[$netWorthLog->getKingdom()->getId()] % count($backgroundColors)];
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['borderWidth'] = 1;
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['data'][$netWorthLog->getTick()] = $netWorthLog->getNetWorth();
        }

        // Zero fill values that don't exist
        foreach ($dataStructure['datasets'] as $kingdomId => $dataSet) {
            foreach ($dataStructure['labels'] as $tick) {
                if (!isset($dataStructure['datasets'][$kingdomId]['data'][$tick])) {
                    $dataStructure['datasets'][$kingdomId]['data'][$tick] = 0;
                }
            }
            sort($dataStructure['datasets'][$kingdomId]['data']);
        }

        $dataStructure['labels'] = array_values($dataStructure['labels']);
        $dataStructure['datasets'] = array_values($dataStructure['datasets']);

        return $dataStructure;
    }

    public function fetchKingdomCompositionData(Kingdom $kingdom)
    {
        $data = [
            'Civilians'  =>
                $this->kingdomManager->lookupResource($kingdom, Resource::CIVILIAN)->getQuantity()
                    +
                $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $this->resourceManager->get(Resource::CIVILIAN))
            ,
            'Materials'  =>
                $this->kingdomManager->lookupResource($kingdom, Resource::MATERIAL)->getQuantity()
                    +
                $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $this->resourceManager->get(Resource::MATERIAL))
            ,
            'Housing'    =>
                $this->kingdomManager->lookupResource($kingdom, Resource::HOUSING)->getQuantity()
                    +
                $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $this->resourceManager->get(Resource::HOUSING))
            ,
            'Military'   =>
                $this->kingdomManager->lookupResource($kingdom, Resource::MILITARY)->getQuantity()
                    +
                $this->em->getRepository(Queue::class)->findTotalQueued($kingdom, $this->resourceManager->get(Resource::MILITARY))
            ,
        ];

        $dataStructure = [
            'labels' => array_keys($data),
            'datasets' => [[
                'label' => $kingdom->getName(),
                'backgroundColor' => "rgba(255,99,132,0.2)",
                'borderColor' => "rgba(255,99,132,1)",
                'pointBackgroundColor' => "rgba(255,99,132,1)",
                'pointBorderColor' => "#fff",
                'pointHoverBackgroundColor' => "#fff",
                'pointHoverBorderColor' => "rgba(255,99,132,1)",
                'data' => array_values($data),
            ]]
        ];

        return $dataStructure;
    }
}