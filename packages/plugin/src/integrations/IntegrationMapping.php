<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

class IntegrationMapping implements IntegrationMappingInterface
{
    /** @var FieldInterface[] */
    private array $fieldMappings = [];

    /**
     * IntegrationMapping constructor.
     */
    public function __construct(
        private Form $form,
        private IntegrationTypeInterface $type,
        private string $resourceId,
        private ResourceFieldCollection $resourceFields,
        array $mappingData
    ) {
        foreach ($mappingData as $fieldName => $expressFieldUid) {
            if (null === $expressFieldUid) {
                continue;
            }

            $field = $form->getFields()->get($expressFieldUid);
            if (!$field) {
                continue;
            }

            $this->fieldMappings[$fieldName] = $field;
        }
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getResourceFields(): ResourceFieldCollection
    {
        return $this->resourceFields;
    }

    public function getHandle(): string
    {
        return $this->getType()->getHandle();
    }

    /**
     * @return FieldInterface[]
     */
    public function getFieldMappings(): array
    {
        return $this->fieldMappings;
    }

    public function getField(string $mappingHandle): ?FieldInterface
    {
        return $this->fieldMappings[$mappingHandle] ?? null;
    }

    public function pushData(array $postedData): bool
    {
        return $this->getType()->pushData($this, $postedData);
    }

    public function jsonSerialize(): array
    {
        $fieldMap = [];

        foreach ($this->getFieldMappings() as $key => $field) {
            $fieldMap[$key] = $field->getUid();
        }

        return [
            'resourceId' => $this->getResourceId(),
            'fieldMap' => $fieldMap,
        ];
    }
}
