<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
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
        $kingdoms = $em->getRepository(Kingdom::class)->findBy(['world' => 1]);

        return [
            'kingdoms' => $kingdoms,
        ];
    }

    /**
     * @Route("/{id}", name="kingdom_stats")
     * @Template
     */
    public function kingdomStatsAction(Kingdom $kingdom)
    {
        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $queues = $kingdomManager->getResourceQueues($kingdom);

        return [
            'kingdom' => $kingdom,
            'queues'  => $queues,
        ];
    }
}
