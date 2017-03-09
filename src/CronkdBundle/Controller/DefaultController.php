<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $kingdoms = $em->getRepository(Kingdom::class)->findBy(['world' => 1]);

        return [
            'kingdoms' => $kingdoms,
        ];
    }
}
