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
     *
     * @param Form                     $form
     * @param IntegrationTypeInterface $type
     * @param string                   $resourceId
     * @param ResourceFieldCollection  $resourceFields
     * @param array                    $mappingData
     */
    public function __construct(
        Form $form,
        IntegrationTypeInterface $type,
        string $resourceId,
        ResourceFieldCollection $resourceFields,
        array $mappingData
    ) {
        $this->form           = $form;
        $this->type           = $type;
        $this->resourceId     = $resourceId;
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
     * @return string
     */
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * @return ResourceFieldCollection
     */
    public function getResourceFields(): ResourceFieldCollection
    {
        return $this->resourceFields;
    }

    /**
     * @return string
     */
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
     * @param string $mappingHandle
     *
     * @return FieldInterface|null
     */
    public function getField(string $mappingHandle)
    {
        return $this->fieldMappings[$mappingHandle] ?? null;
    }

    /**
     * @param array $postedData
     *
     * @return bool
     */
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
            'fieldMap'   => $fieldMap,
        ];
    }
}
