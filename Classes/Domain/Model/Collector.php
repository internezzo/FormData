<?php
namespace Internezzo\FormData\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 * @Flow\Scope("prototype")
 */
class Collector
{
    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $title;

    /**
     * @ORM\OneToMany(mappedBy="collector", orphanRemoval=true, cascade={"persist", "remove"})
     * @var Collection<\Internezzo\FormData\Domain\Model\FormData>
     */
    protected $formData;

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
        return $this->formData->first();
    }

    /**
     * @return FormData
     */
    public function getLastFormData()
    {
        if ($this->formData->isEmpty()) {
            return null;
        }
        return $this->formData->last();
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

}
