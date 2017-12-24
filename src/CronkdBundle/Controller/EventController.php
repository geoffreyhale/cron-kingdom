<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Event\AttackResultEvent;
use CronkdBundle\Entity\Event\ProbeEvent;
use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Event\Event;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Event\ViewLogEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/event")
 */
class EventController extends CronkdController
{
    /**
     * @Route("/{id}", name="event_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Kingdom $kingdom)
    {
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);
        
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository(Event::class)
            ->findBy(['kingdom' => $kingdom,], ['createdAt' => 'DESC',])
        ;

        return [
            'kingdom' => $kingdom,
            'events'  => $events,
        ];
    }

    /**
     * @Route("/probe/{id}", name="event_probe_view")
     * @Method("GET")
     * @Template()
     */
    public function viewProbeAction(ProbeEvent $event)
    {
        $kingdom = $this->extractKingdomFromCurrentUser();
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        if ($event->getKingdom() != $kingdom) {
            throw new AccessDeniedException();
        }

        return [
            'kingdom' => $kingdom,
            'event'   => $event,
            'data'    => json_decode($event->getReportData(), true),
        ];
    }

    /**
     * @Route("/attack/{id}", name="event_attack_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAttackAction(AttackResultEvent $event)
    {
        $kingdom = $this->extractKingdomFromCurrentUser();
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        if ($event->getAttacker() != $kingdom) {
            throw new AccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $resources = $em->getRepository(Resource::class)->findByWorld($kingdom->getWorld());

        return [
            'kingdom'   => $kingdom,
            'event'     => $event,
            'data'      => json_decode($event->getReportData(), true),
            'resources' => $resources,
        ];
    }
}
