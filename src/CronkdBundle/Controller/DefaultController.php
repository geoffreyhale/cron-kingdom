<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
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
        $world = $em->getRepository(World::class)->findOneBy(['active' => true]);
        if (!$world) {
            throw $this->createNotFoundException('No active world found!');
        }

        $user = $this->getUser();
        $userHasKingdom = $em->getRepository(Kingdom::class)->userHasKingdom($user, $world);
        $kingdoms = $em->getRepository(Kingdom::class)->findBy(['world' => $world]);

        return [
            'user'           => $user,
            'world'          => $world,
            'kingdoms'       => $kingdoms,
            'userHasKingdom' => $userHasKingdom,
        ];
    }
}
