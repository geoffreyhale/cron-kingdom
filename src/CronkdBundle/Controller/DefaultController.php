<?php
namespace CronkdBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends CronkdController
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $user = $this->getUser();
        $world = $this->extractActiveWorld();
        if (!$world) {
            return $this->redirect($this->generateUrl('world_index'));
        }
        $kingdom = $this->extractKingdomFromCurrentUser();

        $kingdomState = null;
        if ($kingdom) {
            $kingdomState = $kingdomManager->generateKingdomState($kingdom);
        }

        $worldState = $worldManager->generateWorldState($world);

        return [
            'user'                      => $user,
            'kingdom'                   => $kingdom,
            'kingdomState'              => $kingdomState,
            'world'                     => $world,
            'worldState'                => $worldState,
            'kingdoms'                  => $world->getKingdoms(),
            'userHasKingdom'            => null !== $kingdom,
        ];
    }

    /**
     * @Route("/help", name="help")
     * @Template("CronkdBundle:Help:index.html.twig")
     */
    public function helpAction()
    {
        $settings = $this->getParameter('cronkd.settings');

        return [
            'settings' => $settings,
        ];
    }
}
