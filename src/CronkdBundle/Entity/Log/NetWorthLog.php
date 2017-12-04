<?php
namespace CronkdBundle\Entity\Log;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;

/**
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\NetWorthLogRepository")
 *
 * @Jms\ExclusionPolicy("all")
 */
class NetWorthLog extends Log
{
    /**
     * @var string
     *
     * @ORM\Column(name="net_worth", type="bigint")
     */
    private $netWorth;

    /**
     * Set net worth
     *
     * @param int $netWorth
     *
     * @return NetWorthLog
     */
    public function setNetWorth($netWorth)
    {
        $this->netWorth = $netWorth;

        return $this;
    }

    /**
     * Get net worth
     *
     * @return int
     */
    public function getNetWorth()
    {
        return $this->netWorth;
    }
}
