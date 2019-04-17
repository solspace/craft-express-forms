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
     *
     * @param Form                         $form
     * @param IntegrationMappingCollection $mapping
     */
    public function __construct(Form $form, IntegrationMappingCollection $mapping)
    {
        $this->form              = $form;
        $this->mappingCollection = $mapping;

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
     * @return IntegrationTypeInterface
     */
    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    /**
     * @return IntegrationMappingCollection
     */
    public function getMappingCollection(): IntegrationMappingCollection
    {
        return $this->mappingCollection;
    }
}
