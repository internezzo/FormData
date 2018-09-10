<?php
namespace Internezzo\FormData\Service\DataSource;


use Internezzo\FormData\Domain\Model\Collector;
use Internezzo\FormData\Domain\Repository\CollectorRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Neos\Service\DataSource\AbstractDataSource;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class CollectorDataSource extends AbstractDataSource
{
    /**
     * @var string
     */
    static protected $identifier = 'internezzo-formdata-collectors';

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var CollectorRepository
     */
    protected $collectorRepository;

    /**
     * @param NodeInterface|null $node
     * @param array $arguments
     * @return array
     */
    public function getData(NodeInterface $node = null, array $arguments)
    {
        $options = [];

        $collectors = $this->collectorRepository->findAll();

        /** @var Collector $collector */
        foreach ($collectors as $collector) {
            $options[] = [
                'label' => $collector->getTitle(),
                'value' => $this->persistenceManager->getIdentifierByObject($collector)
            ];
        }
        return $options;
    }
}