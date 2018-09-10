<?php
namespace Internezzo\FormData\Finishers;

use Internezzo\FormData\Domain\Model\Collector;
use Internezzo\FormData\Domain\Repository\CollectorRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;

class FormDataFinisher extends AbstractFinisher
{
    /**
     * @Flow\Inject
     * @var CollectorRepository
     */
    protected $collectorRepository;

    /**
     * @Flow\InjectConfiguration(path="skipElementTypes")
     * @var array
     */
    protected $skipElementTypes;

    /**
     * Executes this finisher
     * @see AbstractFinisher::execute()
     *
     * @return void
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $formDefinition = $formRuntime->getFormDefinition();

        $collectorIdentifier = $this->parseOption('collector');
        /** @var Collector $collector */
        $collector = $this->collectorRepository->findByIdentifier($collectorIdentifier);

        $payLoad = [];

        $formValues = $this->finisherContext->getFormValues();
        foreach ($formValues as $identifier => $value) {
            $element = $formDefinition->getElementByIdentifier($identifier);
            $elementType = $element->getType();
            if (array_key_exists($elementType, $this->skipElementTypes)) {
                if ($this->skipElementTypes[$elementType] === true) {
                    continue;
                }
            }
            $payLoad[$element->getIdentifier()] = $value;
        }

        $collector->addFormData($payLoad);
        $this->collectorRepository->update($collector);
    }
}
