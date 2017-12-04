<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log\Log;
use CronkdBundle\Event\ViewLogEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/log")
 */
class LogController extends CronkdController
{
    /**
     * @Route("/{id}", name="log_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);
        
        $em = $this->getDoctrine()->getManager();
        $logs = $em->getRepository(Log::class)
            ->findBy(['kingdom' => $kingdom,], ['createdAt' => 'DESC',])
        ;

        return [
            'kingdom' => $kingdom,
            'logs'    => $logs,
        ];
    }
}
