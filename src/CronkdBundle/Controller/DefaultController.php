<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\AttackLog;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Log;
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
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $world = $em->getRepository(World::class)->findOneBy(['active' => true]);
        if (!$world) {
            return $this->redirect($this->generateUrl('world_index'));
        }

        $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);
        $userHasKingdom = $em->getRepository(Kingdom::class)->userHasKingdom($user, $world);

        $kingdomResources = [];
        $queues = [];
        $notificationCount = 0;
        $kingdomHasAvailableAttack = false;
        $kingdomHasActivePolicy = false;
        if ($kingdom) {
            $kingdomResources = $em->getRepository(KingdomResource::class)->findByKingdom($kingdom);
            $queues = $this->get('cronkd.manager.kingdom')->getResourceQueues($kingdom);
            $notificationCount = $em->getRepository(Log::class)->findNotificationCount($kingdom);
            $kingdomHasAvailableAttack = $em->getRepository(AttackLog::class)->hasAvailableAttack($kingdom);
            $kingdomWinLoss = $em->getRepository(AttackLog::class)->getWinLossRecord($kingdom);
            $kingdomHasActivePolicy = $this->get('cronkd.manager.policy')->kingdomHasActivePolicy($kingdom);
        }

        return [
            'user'                      => $user,
            'kingdom'                   => $kingdom,
            'queues'                    => $queues,
            'world'                     => $world,
            'worldNetworth'             => $worldManager->calculateWorldNetWorth($world),
            'kingdoms'                  => $world->getKingdoms(),
            'kingdomsByNetworth'        => $kingdomManager->calculateKingdomsByNetWorth($world),
            'kingdomsByWinLoss'         => $kingdomManager->calculateKingdomsByWinLoss($world),
            'kingdomResources'          => $kingdomResources,
            'userHasKingdom'            => $userHasKingdom,
            'notificationCount'         => $notificationCount,
            'kingdomHasAvailableAttack' => $kingdomHasAvailableAttack,
            'kingdomWinLossString'      => $kingdomWinLoss['win'].'-'.$kingdomWinLoss['loss'],
            'kingdomHasActivePolicy'    => $kingdomHasActivePolicy,
        ];
    }

    /**
     * @Route("/help", name="help")
     * @Template("CronkdBundle:Help:index.html.twig")
     */
    public function helpAction()
    {
        return;
    }
}
