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
use Neos\Flow\Security\Authorization\Privilege\Method\MethodPrivilegeSubject;
use Neos\Flow\Security\Authorization\Privilege\PrivilegeInterface;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Security\Exception\AccessDeniedException;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Flow\Security\Policy\Role;

class CollectorController extends ActionController {

    const
        MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER = 'Internezzo.FormData:FormDataModuleAdmin';

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
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var PrivilegeManagerInterface
     */
    protected $privilegeManager;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\InjectConfiguration(package="Neos.Neos", path="modules.management.submodules.formDataModule.privilegeTarget")
     * @var string
     */
    protected $privilegeTargetModule;


    /**
     * List all collectors
     */
    public function indexAction() {

        $collectors = $this->collectorRepository->findAll();

        // Authorization check. This is not done via policy because we define the allowed roles dynamically for the collector
        if ($this->privilegeManager->isPrivilegeTargetGranted(self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER)) {
            // Admins have access
            $visibleCollectors = $collectors;
        } else {
            $visibleCollectors = [];

            foreach ($collectors as $collector) {
                foreach ($collector->getRoleIdentifiers() as $roleIdentifier) {
                    if ($this->securityContext->hasRole($roleIdentifier)) {
                        $visibleCollectors[] = $collector;
                        break;
                    }
                }
            }

        }

        $this->view->assign('collectors', $visibleCollectors);
        $this->view->assign('adminPrivilege', self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER);
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
     * @throws AccessDeniedException
     */
    public function showAction(Collector $collector)
    {
        if (!$this->privilegeManager->isPrivilegeTargetGranted(self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER)) {
            // we check authorization for non-admins. This is not done via policy because we define the allowed roles dynamically for the collector

            $granted = false;
            foreach ($collector->getRoleIdentifiers() as $roleIdentifier) {
                if ($this->securityContext->hasRole($roleIdentifier)) {
                    $granted = true;
                    break;
                }
            }
            if (!$granted) {
                throw new AccessDeniedException('You dont have access to this collector.');
            }
        }

        $rolesWithAccess = [];
        if ($this->privilegeManager->isPrivilegeTargetGranted(self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER)) {
            foreach ($this->policyService->getRoles() as $role) {
                if ($this->hasRoleAccessToPrivilegeTarget($role, self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER)) {
                    // we skip admin roles as they have access anyway
                    continue;
                }
                if ($this->hasRoleAccessToPrivilegeTarget($role, $this->privilegeTargetModule)) {
                    $rolesWithAccess[] = [
                        'role' => $role,
                        'assigned' => in_array($role->getIdentifier(), $collector->getRoleIdentifiers())
                    ];
                }
            }
        }

        $payloadNormalizer = new PayloadNormalizer($collector);
        $cols = $payloadNormalizer->getKeys();

        $this->view->assign('roles', $rolesWithAccess);
        $this->view->assign('collector', $collector);
        $this->view->assign('cols', $cols);
        $this->view->assign('adminPrivilege', self::MODULE_ADMIN_PRIVILEGE_TARGET_IDENTIFIER);
    }

    /**
     * Show the given collector
     *
     * @param Collector $collector
     * @param array $roleIdentifiers
     * @return void
     */
    public function updateAction(Collector $collector, $roleIdentifiers)
    {
        $collector->setRoleIdentifiers($roleIdentifiers);
        $this->collectorRepository->update($collector);
        $this->addFlashMessage(
            $this->translator->translateById('update.flashmessage.success.message', ['collectorTitle' => $collector->getTitle()], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            $this->translator->translateById('update.flashmessage.success.title', [], null, null, 'Modules/FormData', 'Internezzo.FormData'),
            Message::SEVERITY_OK,
            [],
            1546959716
        );

        $this->redirect('show', null, null, ['collector' => $collector]);
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

    /**
     * @param Role $role
     * @param string $privilegeTargetIdentifier
     * @return bool
     */
    protected function hasRoleAccessToPrivilegeTarget(Role $role, $privilegeTargetIdentifier)
    {
        $isGranted = false;
        $isDenied = false;
        $this->checkPrivilegeTargetAuthorisationForRole($role, $privilegeTargetIdentifier, $isGranted, $isDenied);
        if ($isGranted === true && !($isDenied === true)) {
            return true;
        }
        return false;
    }

    /**
     * @param Role $role
     * @param string $privilegeTargetIdentifier
     * @param bool $isGranted
     * @param bool $isDenied
     */
    protected function checkPrivilegeTargetAuthorisationForRole(Role $role, $privilegeTargetIdentifier, &$isGranted, &$isDenied)
    {
        $privilege = $role->getPrivilegeForTarget($privilegeTargetIdentifier);
        if ($privilege instanceof PrivilegeInterface) {
            $isGranted = $privilege->isGranted();
            $isDenied = $privilege->isDenied();
        }

        if ($isDenied) {
            return;
        }

        foreach ($role->getParentRoles() as $parentRole) {
            $this->checkPrivilegeTargetAuthorisationForRole($parentRole, $privilegeTargetIdentifier, $isGrantedForParentRole, $isDenied);
            $isGranted = $isGranted || $isGrantedForParentRole;
            if ($isDenied) {
                return;
            }
        }
    }
}