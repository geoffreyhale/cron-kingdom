<?php
namespace CronkdBundle\Listener;

use CronkdBundle\Entity\Log;
use CronkdBundle\Event\ViewLogEvent;
use Doctrine\ORM\EntityManagerInterface;

class NotificationListener
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param ViewLogEvent $event
     */
    public function onViewLogPage(ViewLogEvent $event)
    {
        $unreadLogs = $this->em->getRepository(Log::class)->findBy([
            'readAt'  => null,
            'kingdom' => $event->kingdom,
        ]);

        foreach ($unreadLogs as $log) {
            $log->setReadAt(new \DateTime());
            $this->em->persist($log);
        }
        $this->em->flush();
    }
}