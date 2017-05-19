<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\WorldType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
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
            'upcomingWorlds' => $em->getRepository(World::class)->findUpcomingWorlds(),
            'activeWorlds'   => $em->getRepository(World::class)->findActiveWorlds(),
            'inactiveWorlds' => $em->getRepository(World::class)->findInactiveWorlds(),
        ];
    }

    /**
     * @Route("/create", name="world_create")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request)
    {
        $world = new World();

        $form = $this->createForm(WorldType::class, $world);
        $form->handleRequest($request);

        // Extra validation
        if ($form->isValid()) {
            if ($world->getStartTime()->getTimestamp() > $world->getEndTime()->getTimestamp()) {
                $form->get('startTime')->addError(new FormError('End time must be later than start time!'));
            }
        }

        if ($form->isValid()) {
            $worldManager = $this->get('cronkd.manager.world');
            $worldManager->create($world);

            $this->get('session')->getFlashBag()->add('success', 'World Created!');

            return $this->redirectToRoute('world_configure', ['world' => $world->getId()]);
        }

        return $this->render('CronkdBundle:World:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{world}/configure", name="world_configure")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function configureAction(World $world)
    {
        return $this->render('CronkdBundle:World:configure.html.twig', [
            'world' => $world,
        ]);
    }

    /**
     * @Route("/{world}/update", name="world_update")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, World $world)
    {
        $form = $this->createForm(WorldType::class, $world);
        $form->handleRequest($request);

        // Extra validation
        if ($form->isValid()) {
            if ($world->getStartTime()->getTimestamp() > $world->getEndTime()->getTimestamp()) {
                $form->get('startTime')->addError(new FormError('End time must be later than start time!'));
            }
        }

        if ($form->isValid()) {
            $worldManager = $this->get('cronkd.manager.world');
            $worldManager->create($world);

            $this->get('session')->getFlashBag()->add('success', 'World Updated!');

            return $this->redirectToRoute('world_configure', ['world' => $world->getId()]);
        }

        return $this->render('CronkdBundle:World:update.html.twig', [
            'form'  => $form->createView(),
            'world' => $world,
        ]);
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

        $worldState = $worldManager->generateWorldState($world);

        return [
            'world'              => $world,
            'worldState'         => $worldState,
            'kingdom'            => $kingdom,
            'worldNetworth'      => $worldManager->calculateWorldNetWorth($world),
            'kingdoms'           => $world->getKingdoms(),
            'kingdomsByNetworth' => $kingdomManager->calculateKingdomsByNetWorth($world),
        ];
    }
}
