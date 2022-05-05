<?php

namespace Solspace\ExpressForms\objects\Export;

use Solspace\ExpressForms\fields\BaseField;
use Solspace\ExpressForms\fields\Checkbox;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\fields\MultipleValueInterface;

abstract class ExportField implements ExportFieldInterface
{
    public function __construct(
        private string $handle,
        private string $label
    ) {
    }

    /**
     * @return ExportField|IntField|StringField
     */
    public static function createFromField(BaseField $field): self
    {
        $suffix = $field->canGetProperty('columnSuffix') ? $field->columnSuffix : '';
        $suffix = $suffix ? '_'.$suffix : '';
        $handle = "c.[[field_{$field->getHandle()}$suffix]]";
        $name = $field->getName();

        if ($field instanceof File) {
            return new AssetField($handle, $name);
        }

        if ($field instanceof Checkbox) {
            return new BooleanField($handle, $name);
        }

        if ($field instanceof MultipleValueInterface) {
            return new ArrayField($handle, $name);
        }

        return new StringField($handle, $name);
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
