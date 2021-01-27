<?php

namespace Solspace\ExpressForms\events\integrations;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\IntegrationMappingCollection;

class MappingAfterBuildFromArrayEvent extends CancelableEvent
{
    /** @var Form */
    private $form;

    /** @var IntegrationTypeInterface */
    private $type;

    /** @var IntegrationMappingCollection */
    private $mappingCollection;

    /**
     * MappingAfterBuildFromArrayEvent constructor.
     */
    public function __construct(Form $form, IntegrationMappingCollection $mapping)
    {
        $this->form = $form;
        $this->mappingCollection = $mapping;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    public function getMappingCollection(): IntegrationMappingCollection
    {
        return $this->mappingCollection;
    }
}
