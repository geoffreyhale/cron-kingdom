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

class DefaultController extends CronkdController
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $user = $this->getUser();
        $world = $this->extractActiveWorld();
        if (!$world) {
            return $this->redirect($this->generateUrl('world_index'));
        }
        $kingdom = $this->extractKingdomFromCurrentUser();

        $kingdomResources = [];
        $queues = [];
        $notificationCount = 0;
        $kingdomHasAvailableAttack = false;
        $kingdomHasActivePolicy = false;
        $kingdomWinLoss = ['win'=>0,'loss'=>0,];
        $policy_end_string = null;
        if ($kingdom) {
            $kingdomResources = $em->getRepository(KingdomResource::class)->findByKingdom($kingdom);
            $queues = $this->get('cronkd.manager.kingdom')->getResourceQueues($kingdom);
            $notificationCount = $em->getRepository(Log::class)->findNotificationCount($kingdom);
            $kingdomHasAvailableAttack = $em->getRepository(AttackLog::class)->hasAvailableAttack($kingdom);
            $kingdomWinLoss = $em->getRepository(AttackLog::class)->getWinLossRecord($kingdom);
            $kingdomHasActivePolicy = $this->get('cronkd.manager.policy')->kingdomHasActivePolicy($kingdom);
            if ($kingdomHasActivePolicy) {
                $policy_end_string = $kingdom->getActivePolicy()->getEndTime()->diff(new \DateTime())->format('%h:%I');
            }
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
            'policy_end_string'         => $policy_end_string,
            'userHasKingdom'            => null !== $kingdom,
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
