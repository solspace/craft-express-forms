<?php

namespace Solspace\ExpressForms\objects\Export;

use Solspace\ExpressForms\fields\BaseField;
use Solspace\ExpressForms\fields\Checkbox;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\fields\MultipleValueInterface;

abstract class ExportField implements ExportFieldInterface
{
    /** @var string */
    private $handle;

    /** @var string */
    private $label;

    /**
     * @param BaseField $field
     *
     * @return ExportField|StringField|IntField
     */
    public static function createFromField(BaseField $field): ExportField
    {
        $handle = "c.[[field_{$field->getHandle()}]]";
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

    /**
     * ExportField constructor.
     *
     * @param string $handle
     * @param string $label
     */
    public function __construct(string $handle, string $label)
    {
        $this->handle = $handle;
        $this->label  = $label;
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
