<?php
namespace Internezzo\FormData\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Security\Policy\PolicyService;

/**
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Collector
{
    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $title;

    /**
     * @var Collection<\Internezzo\FormData\Domain\Model\FormData>
     * @ORM\OneToMany(mappedBy="collector", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\OrderBy({"receivedDateTime"="DESC"})
     */
    protected $formData;

    /**
     * @var array of strings
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $roleIdentifiers = [];

    public function __construct()
    {
        $this->formData = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return Collection<\Internezzo\FormData\Domain\Model\FormData>
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @return FormData
     */
    public function getFirstFormData()
    {
        if ($this->formData->isEmpty()) {
            return null;
        }
        return $this->formData->last();
    }

    /**
     * @return FormData
     */
    public function getLastFormData()
    {
        if ($this->formData->isEmpty()) {
            return null;
        }
        return $this->formData->first();
    }


    /**
     * @param array $payload
     */
    public function addFormData(array $payload)
    {
        $formData = new FormData($this);
        $formData->setPayload($payload);
        $this->formData->add($formData);
    }

    /**
     * @param FormData $formData
     */
    public function removeFormData(FormData $formData)
    {
        $this->formData->removeElement($formData);
    }

    /**
     * @return array
     */
    public function getRoleIdentifiers()
    {
        return $this->roleIdentifiers;
    }

    /**
     * @param array $roleIdentifiers
     */
    public function setRoleIdentifiers(array $roleIdentifiers)
    {
        $this->roleIdentifiers = $roleIdentifiers;
    }

    /**
     * @return array<Role>
     */
    public function getRoles()
    {
        $roles = [];
        foreach ($this->roleIdentifiers as $roleIdentifier) {
            if ($this->policyService->hasRole($roleIdentifier)) {
                $roles[$roleIdentifier] = $this->policyService->getRole($roleIdentifier);
            }
        }
        return $roles;
    }

}
