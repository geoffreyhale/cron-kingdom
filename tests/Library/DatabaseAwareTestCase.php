<?php
namespace Tests\Library;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Fixtures\CronkdFixtures;

abstract class DatabaseAwareTestCase extends KernelTestCase
{
    /** @var  ContainerInterface */
    protected $container;
    /** @var  EntityManagerInterface */
    protected $em;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');

        $conn = $this->em->getConnection();
        $stmt = $conn->executeQuery('SET foreign_key_checks = 0');
        $stmt->execute();

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);

        $loader = new Loader();
        foreach ($this->getRequiredFixtures() as $fixtureClass) {
            $loader->addFixture(new $fixtureClass);
        }

        $executor->execute($loader->getFixtures());
    }

    /**
     * @return array
     */
    public function getRequiredFixtures()
    {
        return [
            CronkdFixtures::class,
        ];
    }
}