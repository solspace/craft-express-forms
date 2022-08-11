<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\models\Form;

class FormIdDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_RENDER_OPENING_TAG, [$this, 'attachFormIdInput']],
        ];
    }

    public function attachFormIdInput(FormRenderTagEvent $event)
    {
        $output = '<input type="hidden" name="formId" value="'.$event->getForm()->getUuid().'" />';

        $event->appendToOutput($output);
    }
}
