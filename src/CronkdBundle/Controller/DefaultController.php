<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource\ResourceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends CronkdController
{
    /**
     * @Route("/", name="home")
     * @Template
     */
    public function indexAction()
    {
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $user = $this->getUser();
        $world = $this->extractActiveWorld();
        if (!$world) {
            return $this->redirect($this->generateUrl('worlds'));
        }
        $kingdom = $this->extractKingdomFromCurrentUser();

        $kingdomState = null;
        if ($kingdom) {
            $kingdomState = $kingdomManager->generateKingdomState($kingdom);
        }

        $worldState = $worldManager->generateWorldState($world);

        $em = $this->getDoctrine()->getManager();
        $resourceTypes = $em->getRepository(ResourceType::class)->findBy([], ['displayOrder' => 'ASC']);

        return [
            'user'             => $user,
            'kingdom'          => $kingdom,
            'kingdomState'     => $kingdomState,
            'world'            => $world,
            'worldState'       => $worldState,
            'kingdoms'         => $world->getKingdoms(),
            'userHasKingdom'   => null !== $kingdom,
            'resourceTypes'    => $resourceTypes,
            'worldPolicies'    => $world->getWorldPolicies(),
        ];
    }

    /**
     * @Route("/help", name="help")
     * @Template("CronkdBundle:Help:index.html.twig")
     */
    public function helpAction()
    {
        $world = $this->extractActiveWorld();
        $resources = $world->getResources();

        $em = $this->getDoctrine()->getManager();
        $resourceTypes = $em->getRepository(ResourceType::class)->findAll();
        $resourceTypes = array_map(function(ResourceType $resourceType) {
            return $resourceType->getName();
        }, $resourceTypes);

        $resourceActionService = $this->get('cronkd.service.resource_action');
        $exponentialTable = $resourceActionService->getExponentialTable();

        return [
            'world'            => $world,
            'resources'        => $resources,
            'resourceTypes'    => $resourceTypes,
            'exponentialTable' => $exponentialTable,
        ];
    }

    /**
     * @Route("/get-world-kingdom-top-navbar-component", name="get_world_kingdom_top_navbar_component")
     */
    public function getWorldKingdomTopNavbarAction()
    {
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $world = $this->extractActiveWorld();
        if (!$world) {
            return $this->redirect($this->generateUrl('worlds'));
        }
        $kingdom = $this->extractKingdomFromCurrentUser();

        $kingdomState = null;
        if ($kingdom) {
            $kingdomState = $kingdomManager->generateKingdomState($kingdom);
        }

        $worldState = $worldManager->generateWorldState($world);

        return $this->render('CronkdBundle:Components:worldKingdomTopNavbar.html.twig',
            [
                'kingdom' => $kingdom,
                'kingdomState' => $kingdomState,
                'world' => $world,
                'worldState' => $worldState,
            ]
        );
    }
}
