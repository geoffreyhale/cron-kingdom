<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\World;

class WorldManager
{
    /**
     * @param World $world
     * @return int
     */
    public function calculateWorldNetWorth(World $world)
    {
        $worldNetWorth = 0;
        foreach ($world->getKingdoms() as $kingdom) {
            $worldNetWorth += $kingdom->getNetworth();
        }

        return $worldNetWorth;
    }
}