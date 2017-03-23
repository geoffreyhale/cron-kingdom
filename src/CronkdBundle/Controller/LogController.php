<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\World;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/log")
 */
class LogController extends Controller
{
    /**
     * @Route("/{id}", name="log_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Kingdom $kingdom)
    {
        $em = $this->getDoctrine()->getManager();
        $logs = $em->getRepository(Log::class)->findBy([
            'kingdom' => $kingdom,
        ], [
            'tick' => 'DESC',
        ]);

        return [
            'kingdom' => $kingdom,
            'logs'    => $logs,
        ];
    }
}
