<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
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

        $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);
        $userHasKingdom = $em->getRepository(Kingdom::class)->userHasKingdom($user, $world);
        $kingdomResources = $em->getRepository(KingdomResource::class)->findByKingdom($kingdom);
        $queues = $this->get('cronkd.manager.kingdom')->getResourceQueues($kingdom);

        return [
            'user'             => $user,
            'kingdom'          => $kingdom,
            'queues'           => $queues,
            'world'            => $world,
            'worldNetworth'    => $worldNetworth,
            'kingdoms'         => $world->getKingdoms(),
            'kingdomResources' => $kingdomResources,
            'userHasKingdom'   => $userHasKingdom,
        ];
    }

    /**
     * @Route("/kingdoms", name="kingdoms")
     * @Template
     */
    public function kingdomsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $kingdoms = $em->getRepository(Kingdom::class)->findBy(['world' => 1]);

        return [
            'kingdoms' => $kingdoms,
        ];
    }

    /**
     * @Route("/users", name="users")
     * @Template
     */
    public function usersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        return [
            'users' => $users,
        ];
    }

    /**
     * @Route("/worlds", name="worlds")
     * @Template
     */
    public function worldsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $worlds = $em->getRepository(World::class)->findAll();

        return [
            'worlds' => $worlds,
        ];
    }
}
