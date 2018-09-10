<?php
namespace Internezzo\FormData\Service;


use Internezzo\FormData\Domain\Model\Collector;
use Internezzo\FormData\Domain\Model\FormData;
use Internezzo\FormData\Domain\Repository\CollectorRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class PayloadNormalizer
{
    /**
     * @var Collector
     */
    protected $collector;

    /**
     * @var array
     */
    protected $keys = false;

    /**
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        if (is_array($this->keys)) {
            return $this->keys;
        }

        $this->keys = [];

        /** @var FormData $formData */
        foreach ($this->collector->getFormData() as $formData) {
            foreach (array_keys($formData->getPayload()) as $curKey) {
                if (!in_array($curKey, $this->getKeys())) {
                    $this->keys[] = $curKey;
                }
            }
        }

        return $this->keys;
    }

    /**
     * @param FormData $formData
     * @return array
     */
    public function getDataRow(FormData $formData)
    {
        if (is_array($this->keys)) {
            $this->getKeys();
        }

        $payload = $formData->getPayload();
        $row = [];
        foreach ($this->keys as $key) {
            if (array_key_exists($key, $payload)) {
                $row[$key] = $payload[$key];
            } else {
                $row[$key] = null;
            }
        }

        return $row;

    }
}