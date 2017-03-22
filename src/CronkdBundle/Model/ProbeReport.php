<?php
namespace CronkdBundle\Model;

use CronkdBundle\Entity\KingdomResource;
use JMS\Serializer\Annotation as Jms;

/**
 * @Jms\ExclusionPolicy("all")
 */
class ProbeReport
{
    /**
     * @var  bool
     *
     * @Jms\Expose()
     */
    private $result = false;

    /**
     * @var KingdomResource[]
     *
     * @Jms\Expose()
     */
    private $data = [];

    /**
     * @param bool $result
     * @return ProbeReport
     */
    public function setResult(bool $result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return bool
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $data
     * @return ProbeReport
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return KingdomResource[]
     */
    public function getData()
    {
        return $this->data;
    }
}