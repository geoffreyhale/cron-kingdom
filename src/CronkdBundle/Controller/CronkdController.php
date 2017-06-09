<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\WorldNotActiveException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronkdController extends Controller
{
    /**
     * @return Kingdom|null
     */
    public function extractKingdomFromCurrentUser()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $world = $em->getRepository(World::class)->findActiveWorld();
        if (!$world) {
            return null;
        }

        /** @var Kingdom $kingdom */
        $kingdom = $em->getRepository(Kingdom::class)->findOneByUserWorld($user, $world);

        return $kingdom;
    }

    /**
     * @return null|World
     */
    public function extractActiveWorld()
    {
        $em = $this->getDoctrine()->getManager();
        $world = $em->getRepository(World::class)->findActiveWorld();
        if (!$world) {
            return null;
        }

        return $world;
    }

    /**
     * @param Kingdom $kingdom
     * @throws WorldNotActiveException
     */
    public function validateWorldIsActive(Kingdom $kingdom)
    {
        $world = $kingdom->getWorld();
        if (!$world->getActive()) {
            throw new WorldNotActiveException($world->getName());
        }
    }

    /**
     * @param Kingdom $kingdom
     */
    public function validateUserOwnsKingdom(Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('Kingdom is not yours!');
        }
    }
}
