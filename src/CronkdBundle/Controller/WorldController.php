<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\World;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/world")
 */
class WorldController extends Controller
{
    /**
     * @Route("/{id}/show", name="world_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(World $world)
    {
        return [
            'world' => $world,
        ];
    }
}
