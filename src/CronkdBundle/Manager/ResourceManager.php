<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\ResourceType;
use CronkdBundle\Exceptions\InvalidResourceException;
use Doctrine\ORM\EntityManagerInterface;

class ResourceManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var array  */
    private $settings;
    /** @var  array */
    private $cachedResources;

    public function __construct(EntityManagerInterface $em, array $settings)
    {
        $this->em              = $em;
        $this->settings        = $settings;
        $this->cachedResources = [];
    }

    /**
     * @return array
     */
    public function getKingdomStartingResources()
    {
        $initialResources = [];
        foreach ($this->settings['resources'] as $resourceName => $resourceData) {
            $initialResources[$resourceName] = $resourceData['initial'];
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
}