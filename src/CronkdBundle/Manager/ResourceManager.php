<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Resource;
use CronkdBundle\Exceptions\InvalidResourceException;
use Doctrine\ORM\EntityManagerInterface;

class ResourceManager
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var array  */
    private $cronKdSettings;
    /** @var  array */
    private $cachedResources;

    public function __construct(EntityManagerInterface $em, array $cronKdSettings)
    {
        $this->em              = $em;
        $this->cronKdSettings  = $cronKdSettings;
        $this->cachedResources = [];
    }

    /**
     * @return array
     */
    public function getKingdomStartingResources()
    {
        $initialResources = [];
        foreach ($this->cronKdSettings['resources'] as $resourceName => $resourceData) {
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
}