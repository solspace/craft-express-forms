<?php

namespace Solspace\ExpressForms\events\integrations;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\models\Form;

class MappingBuildFromArrayEvent extends CancelableEvent
{
    /** @var Form */
    private $form;

    /** @var array */
    private $mappingData;

    /**
     * MappingBuildFromArrayEvent constructor.
     */
    public function __construct(Form $form, array $mappingData)
    {
        $this->form = $form;
        $this->mappingData = $mappingData;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getMappingData(): array
    {
        return $this->mappingData ?? [];
    }

    public function setMappingData(array $mappingData): self
    {
        $this->mappingData = $mappingData;

        return $this;
    }
}
