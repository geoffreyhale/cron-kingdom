<?php
namespace CronkdBundle\Service;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Queue;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Exceptions\InvalidQueueIntervalException;
use Doctrine\ORM\EntityManagerInterface;

class QueuePopulator
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function build(Kingdom $kingdom, Resource $resource, $intervals, $quantity)
    {
        if (0 >= $intervals || !is_int($intervals)) {
            throw new InvalidQueueIntervalException($intervals);
        }

        $world = $kingdom->getWorld();
        $worldTick = $world->getTick();

        $queues = [];
        for ($i = 1; $i <= $intervals; $i++) {
            $currentTick = $worldTick + $i;
            $queue = $this->em->getRepository(Queue::class)->findOneBy([
                'tick'     => $currentTick,
                'kingdom'  => $kingdom,
                'resource' => $resource,
            ]);
            if (!$queue) {
                $queue = new Queue();
                $queue->setTick($currentTick);
                $queue->setKingdom($kingdom);
                $queue->setResource($resource);
                $queue->setQuantity(0);
            }

            $queues[$currentTick] = $queue;
        }

        $queues = array_reverse($queues, false);

        while (true) {
            /** @var Queue $queue */
            foreach ($queues as $queue) {
                if (0 == $quantity) {
                    break;
                }

                $queue->addQuantity(1);
                $quantity--;
            }
            if (0 == $quantity) {
                break;
            }
        }

        $queues = array_reverse($queues, false);
        foreach ($queues as $queue) {
            $this->em->persist($queue);
        }
        $this->em->flush();

        return $queues;
    }

    public function lump(Kingdom $kingdom, Resource $resource, $tickDelay, $quantity)
    {
        if (0 >= $tickDelay || !is_int($tickDelay)) {
            throw new InvalidQueueIntervalException($tickDelay);
        }

        $world = $kingdom->getWorld();
        $worldTick = $world->getTick();

        $currentTick = $worldTick + $tickDelay;
        $queue = $this->em->getRepository(Queue::class)->findOneBy([
            'tick'     => $currentTick,
            'kingdom'  => $kingdom,
            'resource' => $resource,
        ]);
        if (!$queue) {
            $queue = new Queue();
            $queue->setTick($currentTick);
            $queue->setKingdom($kingdom);
            $queue->setResource($resource);
            $queue->setQuantity(0);
        }

        $queue->addQuantity($quantity);

        $this->em->persist($queue);
        $this->em->flush();

        $queues[$currentTick] = $queue;

        return $queues;
    }
}