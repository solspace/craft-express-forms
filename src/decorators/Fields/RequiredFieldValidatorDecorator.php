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

    /**
     * @param FieldValidateEvent $event
     */
    public function validateRequiredField(FieldValidateEvent $event)
    {
        $field = $event->getField();

        if ($field instanceof File) {
            return;
        }

        if ($field->isRequired() && empty(trim($field->getValue()))) {
            $field->addValidationError('This field is required');
        }
    }
}
