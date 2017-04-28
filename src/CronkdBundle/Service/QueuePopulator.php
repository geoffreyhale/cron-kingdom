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

        /** @var Queue $queue */
        $queues = array_values($queues);
        foreach ($queues as $index => $queue) {
            $addition = floor($quantity / $intervals);
            $remainderForThisQueue = $quantity % $intervals;
            if ($remainderForThisQueue >= ($intervals - $index)) {
                $addition++;
            }
            $queue->addQuantity($addition);
            $this->em->persist($queue);
        }

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