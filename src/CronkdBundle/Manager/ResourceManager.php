<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceHousing;
use CronkdBundle\Entity\Resource\ResourceType;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\InvalidResourceException;
use Doctrine\ORM\EntityManagerInterface;

class ResourceManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var  array */
    private $cachedResources;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em              = $em;
        $this->cachedResources = [];
    }

    /**
     * @param World $world
     * @return Resource[]
     */
    public function getWorldResources(World $world)
    {
        $resources = $this->em->getRepository(Resource::class)->findByWorld($world);

        return $resources;
    }

    /**
     * @param World $world
     * @return array
     */
    public function getKingdomStartingResources(World $world)
    {
        $resources        = $this->getWorldResources($world);
        $initialResources = [];

        /** @var Resource $resource */
        foreach ($resources as $resource) {
            $initialResources[$resource->getName()] = $resource->getStartingAmount();
        }

        return $initialResources;
    }

    /**
     * @param string $resourceName
     * @return Resource
     * @throws InvalidResourceException
     */
    public function get(string $resourceName)
    {
        if (!isset($this->cachedResources[$resourceName])) {
            $resource = $this->em->getRepository(Resource::class)->findOneByName($resourceName);
            if (!$resource) {
                throw new InvalidResourceException($resourceName);
            }
            $this->cachedResources[$resourceName] = $resource;
        }

        return $this->cachedResources[$resourceName];
    }

    /**
     * @return array
     */
    public function getPopulationResources()
    {
        $resources = $this->em->getRepository(Resource::class)
            ->findResourcesByType(ResourceType::POPULATION);

        return $resources;
    }

    /**
     * @return array
     */
    public function getBuildingResources()
    {
        $resources = $this->em->getRepository(Resource::class)
            ->findResourcesByType(ResourceType::BUILDING);

        return $resources;
    }

    /**
     * Need to get away from hardcoding "Civilian" as the base population resource.
     * Treat the first population resource that cannot be produced as the base population resource
     * and return that.
     *
     * @return Resource|null
     */
    public function getBasePopulationResource()
    {
        $populationResources = $this->getPopulationResources();
        foreach ($populationResources as $resource) {
            if (!$resource->getCanBeProduced()) {
                return $resource;
            }
        }

        return null;
    }

    /**
     *
     * @return array
     */
    public function getCapacityResources()
    {
        $resourcesByCapacity = [];
        $buildingResources = $this->getBuildingResources();
        foreach ($buildingResources as $buildingResource) {
            if ($buildingResource->getCapacity() > 0) {
                $resourcesByCapacity[$buildingResource->getName()] = count($buildingResource->getHousing());
            }
        }

        /*
        usort($resourcesByCapacity, function ($item1, $item2) {
            return count($item2) <=> count($item1);
        });
        foreach ($resourcesByCapacity as $v => $thing) {
            dump($v, $thing);
        }
        */
        dump($resourcesByCapacity);
        asort($resourcesByCapacity);
        dump($resourcesByCapacity);
        die();

        return [];
    }
}