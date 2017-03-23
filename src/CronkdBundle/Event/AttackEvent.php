<?php
namespace CronkdBundle\Event;

use CronkdBundle\Entity\Kingdom;
use Symfony\Component\EventDispatcher\Event;

class AttackEvent extends Event
{
    /** @var Kingdom  */
    public $kingdom;
    /** @var Kingdom */
    public $target;

    public function __construct(Kingdom $kingdom, Kingdom $target)
    {
        $this->kingdom = $kingdom;
        $this->target  = $target;
    }
}