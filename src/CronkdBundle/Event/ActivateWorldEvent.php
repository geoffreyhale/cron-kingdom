<?php
namespace CronkdBundle\Event;

use CronkdBundle\Entity\World;
use Symfony\Component\EventDispatcher\Event;

class ActivateWorldEvent extends Event
{
    /** @var World  */
    public $world;

    public function __construct(World $world)
    {
        $this->world = $world;
    }
}