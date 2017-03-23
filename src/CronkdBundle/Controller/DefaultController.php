<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\User;
use CronkdBundle\Entity\World;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $world = $em->getRepository(World::class)->findOneBy(['active' => true]);
        if (!$world) {
            throw $this->createNotFoundException('No active world found!');
        }

        $worldNetworth = 0;
        foreach ($world->getKingdoms() as $kingdom) {
            $worldNetworth += $kingdom->getNetworth();
        }

        $kingdomsByNetworth = $world->getKingdoms()->toArray();
        usort($kingdomsByNetworth, function ($item1, $item2) {
            return $item2->getNetworth() <=> $item1->getNetworth();
        });

        $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);
        $userHasKingdom = $em->getRepository(Kingdom::class)->userHasKingdom($user, $world);

        $kingdomResources = [];
        $queues = [];
        $recentLogs = [];
        if ($kingdom) {
            $kingdomResources = $em->getRepository(KingdomResource::class)->findByKingdom($kingdom);
            $queues = $this->get('cronkd.manager.kingdom')->getResourceQueues($kingdom);
            $recentLogs = $em->getRepository(Log::class)->findByRecent($kingdom, 10);
        }

        return [
            'user'             => $user,
            'kingdom'          => $kingdom,
            'queues'           => $queues,
            'world'            => $world,
            'worldNetworth'    => $worldNetworth,
            'kingdoms'         => $world->getKingdoms(),
            'kingdomsByNetworth' => $kingdomsByNetworth,
            'kingdomResources' => $kingdomResources,
            'userHasKingdom'   => $userHasKingdom,
            'recentLogs'       => $recentLogs,
        ];
    }
}
