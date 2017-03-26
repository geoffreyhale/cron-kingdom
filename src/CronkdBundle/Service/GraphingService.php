<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\NetWorthLog;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\EmptyGraphingDatasetException;
use Doctrine\ORM\EntityManagerInterface;

class GraphingService
{
    /** @var EntityManagerInterface  */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param World $world
     * @return array
     * @throws EmptyGraphingDatasetException
     */
    public function fetchNetWorthGraphData(World $world)
    {
        $data = $this->em->getRepository(NetWorthLog::class)
            ->findByWorld($world, $world->getTick()-25, $world->getTick());
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
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['backgroundColor'] = [
                $backgroundColors[$players[$netWorthLog->getKingdom()->getId()] % count($backgroundColors)],
            ];
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['borderColor'] = [
                $borderColors[$players[$netWorthLog->getKingdom()->getId()] % count($backgroundColors)],
            ];
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['borderWidth'] = 1;
            $dataStructure['datasets'][$netWorthLog->getKingdom()->getId()]['data'][] = $netWorthLog->getNetWorth();
        }

        $dataStructure['labels'] = array_values($dataStructure['labels']);
        $dataStructure['datasets'] = array_values($dataStructure['datasets']);

        return $dataStructure;
    }
}