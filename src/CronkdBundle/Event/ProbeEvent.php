<?php
namespace CronkdBundle\Event;

use CronkdBundle\Entity\Kingdom;
use Symfony\Component\EventDispatcher\Event;

class ProbeEvent extends Event
{
    /** @var Kingdom  */
    public $kingdom;

    public function __construct(Kingdom $kingdom)
    {
        $this->kingdom = $kingdom;
    }
}