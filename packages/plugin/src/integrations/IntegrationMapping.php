<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

class IntegrationMapping implements IntegrationMappingInterface
{
    /** @var Form */
    private $form;

    /** @var IntegrationTypeInterface */
    private $type;

    /** @var string */
    private $resourceId;

    /** @var ResourceFieldCollection */
    private $resourceFields;

    /** @var FieldInterface[] */
    private $fieldMappings = [];

    /**
     * IntegrationMapping constructor.
     */
    public function __construct(
        Form $form,
        IntegrationTypeInterface $type,
        string $resourceId,
        ResourceFieldCollection $resourceFields,
        array $mappingData
    ) {
        $this->form = $form;
        $this->type = $type;
        $this->resourceId = $resourceId;
        $this->resourceFields = $resourceFields;

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

    /**
     * @return null|FieldInterface
     */
    public function getField(string $mappingHandle)
    {
        return $this->fieldMappings[$mappingHandle] ?? null;
    }

    public function pushData(array $postedData): bool
    {
        return $this->getType()->pushData($this, $postedData);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
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
