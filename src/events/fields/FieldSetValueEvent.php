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
     * @param FieldInterface $field
     * @param mixed          $value
     */
    public function __construct(FieldInterface $field, $value)
    {
        $this->field = $field;
        $this->value = $value;

        parent::__construct();
    }

    /**
     * @return FieldInterface
     */
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
     *
     * @return FieldSetValueEvent
     */
    public function setValue($value): FieldSetValueEvent
    {
        $this->value = $value;

        return $this;
    }
}
