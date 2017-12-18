<?php
namespace Tests\Library;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\World;

class CronkdDatabaseAwareTestCase extends DatabaseAwareTestCase
{
    public function fetchResource(string $name, World $world = null)
    {
        if (null === $world) {
            return $this->em->getRepository(Resource::class)->findOneByName($name);
        }

        return $this->em->getRepository(Resource::class)->findOneBy([
            'world' => $world,
            'name'  => $name,
        ]);
    }

    public function fetchKingdom(string $name, World $world = null)
    {
        if (null === $world) {
            return $this->em->getRepository(Kingdom::class)->findOneByName($name);
        }

        return $this->em->getRepository(Kingdom::class)->findOneBy([
            'world' => $world,
            'name'  => $name,
        ]);
    }

    public function fillKingdomResources(Kingdom $kingdom, array $kingdomResources)
    {
        foreach ($kingdomResources as $resourceName => $quantity) {
            $kingdomResource = new KingdomResource();
            $kingdomResource->setKingdom($kingdom);
            $kingdomResource->setResource($this->fetchResource($resourceName));
            $kingdomResource->setQuantity($quantity);
            $kingdom->addResource($kingdomResource);
            $this->em->persist($kingdomResource);
        }
        $this->em->flush();

        return $kingdom;
    }
}