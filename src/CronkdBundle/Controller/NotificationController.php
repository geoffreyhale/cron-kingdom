<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log\Log;
use CronkdBundle\Entity\Notification\Notification;
use CronkdBundle\Event\ViewLogEvent;
use CronkdBundle\Event\ViewNotificationsEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/notification")
 */
class NotificationController extends CronkdController
{
    /**
     * @Route("/{id}", name="notification_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository(Notification::class)->findByKingdom($kingdom);

        $templateData = $this->renderView('CronkdBundle:Notification:index.html.twig', [
            'kingdom'       => $kingdom,
            'notifications' => $notifications,
        ]);

        $event = new ViewNotificationsEvent($kingdom);
        $this->get('event_dispatcher')->dispatch('event.view_notifications', $event);

        return new Response($templateData);
    }
}
