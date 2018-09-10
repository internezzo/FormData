<?php

namespace Internezzo\FormData\Controller\Backend;

use Internezzo\FormData\Domain\Model\Collector;
use Internezzo\FormData\Domain\Model\FormData;
use Internezzo\FormData\Domain\Repository\CollectorRepository;
use Internezzo\FormData\Service\PayloadNormalizer;
use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\I18n\Translator;
use Neos\Flow\Mvc\Controller\ActionController;

class CollectorController extends ActionController {

    /**
     * @Flow\Inject
     * @var Translator
     */
    protected $translator;

    /**
     * @Flow\Inject
     * @var CollectorRepository
     */
    protected $collectorRepository;

    /**
     * List all collectors
     */
    public function indexAction() {
        $collectors = $this->collectorRepository->findAll();
        $this->view->assign('collectors', $collectors);
    }

    /**
     * Show a form to create a new collection
     *
     * @param Collector|null $collector
     * @return void
     */
    public function newAction(Collector $collector = null)
    {
        $this->view->assign('collector', $collector);
    }

    /**
     * Create a new collection
     *
     * @param Collector $collector
     * @return void
     */
    public function createAction(Collector $collector)
    {
        $this->collectorRepository->add($collector);
        $this->addFlashMessage('The collector "%s" has been created.', 'Collector created', Message::SEVERITY_OK, array(htmlspecialchars($collector->getTitle())), 1536241316);
        $this->redirect('index');
    }

    /**
     * Show the given collector
     *
     * @param Collector $collector
     * @return void
     */
    public function showAction(Collector $collector)
    {
        $payloadNormalizer = new PayloadNormalizer($collector);
        $cols = $payloadNormalizer->getKeys();

        $this->view->assign('collector', $collector);
        $this->view->assign('cols', $cols);
    }

    /**
     * Sends the data of the given collector as csv download
     *
     * @param Collector $collector
     * @return void
     */
    public function downloadAsCsvAction(Collector $collector)
    {
        $filename = str_replace(' ', '_',$collector->getTitle()).'.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);

        $payloadNormalizer = new PayloadNormalizer($collector);
        $cols = $payloadNormalizer->getKeys();
        $cols[] = 'received';

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        // output the column headings
        fputcsv($output, $cols);
        /** @var FormData $formData */
        foreach ($collector->getFormData() as $formData) {
            $data = $payloadNormalizer->getDataRow($formData);
            $data['received'] = $formData->getReceivedDateTime()->format('Y-m-d H:i:s');
            fputcsv($output, $data);
        }
        exit;
    }

    /**
     * Delete the given collector
     *
     * @param Collector $collector
     * @return void
     */
    public function deleteAction(Collector $collector)
    {
        $this->collectorRepository->remove($collector);
        $this->addFlashMessage(
            $this->translator->translateById('delete.flashmessage.success.message', [$collector->getTitle()], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            $this->translator->translateById('delete.flashmessage.success.title', [], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            Message::SEVERITY_OK,
            [],
            1536239789
        );
        $this->redirect('index');
    }

    /**
     * Delete the given collector
     *
     * @param FormData $formData
     * @return void
     */
    public function deleteFormDataAction(FormData $formData)
    {
        $collector = $formData->getCollector();
        $collector->removeFormData($formData);
        $this->collectorRepository->update($collector);
        $this->addFlashMessage(
            $this->translator->translateById('formdata.delete.flashmessage.success.message', [], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            $this->translator->translateById('formdata.delete.flashmessage.success.title', [], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            Message::SEVERITY_OK,
            [],
            1536247172
        );
        $this->redirect('show', null, null, ['collector' => $collector]);
    }
}