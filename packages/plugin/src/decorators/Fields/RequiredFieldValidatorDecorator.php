<?php

namespace Solspace\ExpressForms\decorators\Fields;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\fields\FieldValidateEvent;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\models\Form;

class RequiredFieldValidatorDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_VALIDATE_FIELD, [$this, 'validateRequiredField']],
        ];
    }

    public function validateRequiredField(FieldValidateEvent $event)
    {
        $field = $event->getField();

        if (!$field->isRequired()) {
            return;
        }

        if ($field instanceof File) {
            return;
        }

        $value = $field->getValue();

        if (!\is_array($value)) {
            $value = trim($value);
        }

        if (!empty($value)) {
            return;
        }

        $field->addValidationError('This field is required');
    }
}
