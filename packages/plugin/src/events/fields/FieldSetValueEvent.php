<?php

namespace Solspace\ExpressForms\events\fields;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\fields\FieldInterface;

class FieldSetValueEvent extends CancelableEvent
{
    /** @var FieldInterface */
    private $field;

    /** @var mixed */
    private $value;

    /**
     * FieldSetValueEvent constructor.
     *
     * @param mixed $value
     */
    public function __construct(FieldInterface $field, $value)
    {
        $this->field = $field;
        $this->value = $value;

        parent::__construct();
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
