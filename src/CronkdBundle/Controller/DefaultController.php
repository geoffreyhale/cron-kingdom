<?php

namespace CronkdBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("CronkdBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        return ['something' => 'value'];
    }
}
