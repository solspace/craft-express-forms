<?php

namespace Solspace\ExpressForms\decorators\Fields;

use Solspace\ExpressForms\decorators\AbstractTranslatableDecorator;
use Solspace\ExpressForms\events\fields\FieldValidateEvent;
use Solspace\ExpressForms\fields\Email;
use Solspace\ExpressForms\models\Form;

class EmailFieldValidatorDecorator extends AbstractTranslatableDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_VALIDATE_FIELD, [$this, 'validateEmailField']],
        ];
    }

    public function validateEmailField(FieldValidateEvent $event): void
    {
        $field = $event->getField();

        if ($field instanceof Email && !empty(trim($field->getValue()))) {
            $value = trim($field->getValue());

            if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
                $field->addValidationError($this->translate('Email address is not valid'));
            }
        }
    }
}
