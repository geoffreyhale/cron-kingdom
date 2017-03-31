<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\ProbeAttemptType;
use CronkdBundle\Model\ProbeAttempt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
            'activeWorlds'   => $em->getRepository(World::class)->findByActive(true),
            'inactiveWorlds' => $em->getRepository(World::class)->findByActive(false),
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
