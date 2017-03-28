<?php
namespace CronkdBundle\Manager;

use CronkdBundle\Entity\Resource;
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