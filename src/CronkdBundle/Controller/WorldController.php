<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/world")
 */
class WorldController extends Controller
{
    /**
     * @Route("/", name="world_index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return [
            'upcomingWorlds' => $em->getRepository(World::class)->findUpcomingWorlds(),
            'activeWorlds'   => $em->getRepository(World::class)->findActiveWorlds(),
            'inactiveWorlds' => $em->getRepository(World::class)->findInactiveWorlds(),
        ];
    }

    /**
     * @Route("/{id}", name="world_show")
     * @ParamConverter(name="id", class="CronkdBundle:World")
     * @Template()
     */
    public function showAction(World $world)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $worldManager = $this->get('cronkd.manager.world');
        $kingdomManager = $this->get('cronkd.manager.kingdom');

        $kingdom = null;
        if (null !== $user) {
            $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);
        }

        return [
            'world'              => $world,
            'kingdom'            => $kingdom,
            'worldNetworth'      => $worldManager->calculateWorldNetWorth($world),
            'kingdoms'           => $world->getKingdoms(),
            'kingdomsByNetworth' => $kingdomManager->calculateKingdomsByNetWorth($world),
        ];
    }
}
