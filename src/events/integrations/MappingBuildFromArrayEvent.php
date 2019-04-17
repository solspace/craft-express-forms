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
     *
     * @param Form  $form
     * @param array $mappingData
     */
    public function __construct(Form $form, array $mappingData)
    {
        $this->form        = $form;
        $this->mappingData = $mappingData;

        parent::__construct();
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getMappingData(): array
    {
        return $this->mappingData ?? [];
    }

    /**
     * @param array $mappingData
     *
     * @return MappingBuildFromArrayEvent
     */
    public function setMappingData(array $mappingData): MappingBuildFromArrayEvent
    {
        $this->mappingData = $mappingData;

        return $this;
    }
}
