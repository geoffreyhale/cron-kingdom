<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChatController extends Controller
{
    /**
     * @Route("/chat", name="chat")
     * @Template
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $world = $em->getRepository(World::class)->findOneBy(['active' => true]);
        $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);

        return [
            'kingdom' => $kingdom
        ];
    }
}
