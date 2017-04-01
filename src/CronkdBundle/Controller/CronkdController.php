<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Exceptions\WorldNotActiveException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronkdController extends Controller
{
    public function validateWorldIsActive(Kingdom $kingdom)
    {
        $world = $kingdom->getWorld();
        if (!$world->getActive()) {
            throw new WorldNotActiveException($world->getName());
        }
    }

    public function validateUserOwnsKingdom(Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }
    }
}
