<?php

use CronkdBundle\Entity\World;
use PHPUnit\Framework\TestCase;

class WorldTest extends TestCase
{
    public function testInstantiation()
    {
        $world = new World();

        $this->assertEquals(World::class, get_class($world));
    }

    public function isWorldActiveDataProvider()
    {
        return [
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P1M')),
                true,
            ],
            [
                null,
                null,
                false,
            ],
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                null,
                true,
            ],
            [
                (new DateTime())->add(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P2M')),
                false,
            ],
            [
                (new DateTime())->sub(new DateInterval('P2M')),
                (new DateTime())->sub(new DateInterval('P1M')),
                false,
            ],
        ];
    }

    public function isWorldUpcomingDataProvider()
    {
        return [
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P1M')),
                false,
            ],
            [
                null,
                null,
                false,
            ],
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                null,
                false,
            ],
            [
                (new DateTime())->add(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P2M')),
                true,
            ],
            [
                (new DateTime())->add(new DateInterval('P1M')),
                null,
                true,
            ],
            [
                (new DateTime())->sub(new DateInterval('P2M')),
                (new DateTime())->sub(new DateInterval('P1M')),
                false,
            ],
        ];
    }

    /**
     * @dataProvider isWorldActiveDataProvider
     */
    public function testIsWorldActive($start, $end, $isActive)
    {
        $world = new World();

        if (null !== $start) {
            $world->setStartTime($start);
        }
        if (null !== $end) {
            $world->setEndTime($end);
        }

        $this->assertEquals($isActive, $world->isActive());
    }

    /**
     * @dataProvider isWorldUpcomingDataProvider
     */
    public function testIsWorldUpcoming($start, $end, $isActive)
    {
        $world = new World();

        if (null !== $start) {
            $world->setStartTime($start);
        }
        if (null !== $end) {
            $world->setEndTime($end);
        }

        $this->assertEquals($isActive, $world->isUpcoming());
    }

    public function isWorldInactiveDataProvider()
    {
        return [
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P1M')),
                false,
            ],
            [
                null,
                null,
                false,
            ],
            [
                (new DateTime())->sub(new DateInterval('P1M')),
                null,
                false,
            ],
            [
                (new DateTime())->add(new DateInterval('P1M')),
                (new DateTime())->add(new DateInterval('P2M')),
                false,
            ],
            [
                (new DateTime())->sub(new DateInterval('P2M')),
                (new DateTime())->sub(new DateInterval('P1M')),
                true,
            ],
        ];
    }

    /**
     * @dataProvider isWorldInactiveDataProvider
     */
    public function testIsWorldInactive($start, $end, $isActive)
    {
        $world = new World();

        if (null !== $start) {
            $world->setStartTime($start);
        }
        if (null !== $end) {
            $world->setEndTime($end);
        }

        $this->assertEquals($isActive, $world->isInactive());
    }
}