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

    /** @var bool */
    public $result;

    public function __construct(Kingdom $kingdom, Kingdom $target, int $result)
    {
        $this->kingdom = $kingdom;
        $this->target  = $target;
        $this->result  = 1 === $result ? true : false;
    }
}