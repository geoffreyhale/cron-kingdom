<?php
namespace CronkdBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/map")
 */
class MapController extends Controller
{
    /**
     * @Route("", name="map")
     * @Template("CronkdBundle:Map:index.html.twig")
     */
    public function indexAction() {
        return;
    }
}