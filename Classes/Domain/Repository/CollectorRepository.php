<?php
namespace Internezzo\FormData\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\QueryInterface;

/**
 * @Flow\Scope("singleton")
 */
class CollectorRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'title' => QueryInterface::ORDER_ASCENDING
    );

}
