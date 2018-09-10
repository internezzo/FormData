<?php
namespace Internezzo\FormData\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class FormData
{
    /**
     * @var Collector
     * @ORM\ManyToOne(inversedBy="formData")
     */
    protected $collector;

    /**
     * @var array<mixed>
     * @ORM\Column(type="flow_json_array")
     */
    protected $payload = array();

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $receivedDateTime;

    /**
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->receivedDateTime = new \DateTime();
        $this->collector = $collector;
    }

    /**
     * @return Collector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return \DateTime
     */
    public function getReceivedDateTime()
    {
        return $this->receivedDateTime;
    }
}
