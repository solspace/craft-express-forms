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

    public function addField(ExportFieldInterface $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param ExportFieldInterface[] $fields
     */
    public function setFields(array $fields): self
    {
        $this->fields = [];
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }
}
