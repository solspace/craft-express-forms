<?php

namespace Solspace\ExpressForms\events\fields;

use Solspace\ExpressForms\fields\FieldInterface;
use yii\base\Event;

class FieldValidateEvent extends Event
{
    /** @var FieldInterface */
    private $field;

    /**
     * FieldValidateEvent constructor.
     */
    public function __construct(FieldInterface $field)
    {
        $this->field = $field;

        parent::__construct();
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }
}
