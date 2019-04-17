<?php

namespace Solspace\ExpressForms\events\export;

use Solspace\ExpressForms\objects\Export\ExportFieldInterface;
use yii\base\Event;

class CompileExportableFields extends Event
{
    /** @var ExportFieldInterface[] */
    private $fields;

    /**
     * CompileExportableFields constructor.
     *
     * @param ExportFieldInterface[] $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;

        parent::__construct();
    }

    /**
     * @return ExportFieldInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param ExportFieldInterface $field
     *
     * @return CompileExportableFields
     */
    public function addField(ExportFieldInterface $field): CompileExportableFields
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param ExportFieldInterface[] $fields
     *
     * @return CompileExportableFields
     */
    public function setFields(array $fields): CompileExportableFields
    {
        $this->fields = [];
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }
}
