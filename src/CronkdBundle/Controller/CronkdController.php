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

    public function validateUserNotVacation()
    {
        if ($this->getUser()->getVacation()){
            throw $this->createAccessDeniedException('You are on vacation!');
        }
    }

    public function validateUserOwnsKingdom(Kingdom $kingdom)
    {
        if ($this->getUser() != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }
    }
}
