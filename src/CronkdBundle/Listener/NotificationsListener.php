<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Notification\Notification;
use CronkdBundle\Event\ViewNotificationsEvent;
use Doctrine\ORM\EntityManagerInterface;

class NotificationsListener
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param ViewNotificationsEvent $event
     */
    public function onViewNotificationsPage(ViewNotificationsEvent $event)
    {
        $unread = $this->em->getRepository(Notification::class)->findBy([
            'readAt'  => null,
            'kingdom' => $event->kingdom,
        ]);

        foreach ($unread as $notification) {
            $notification->setReadAt(new \DateTime());
            $this->em->persist($notification);
        }
        $this->em->flush();
    }
}