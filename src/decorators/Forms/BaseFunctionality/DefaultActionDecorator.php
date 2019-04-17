<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\models\Form;

class DefaultActionDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_RENDER_OPENING_TAG, [$this, 'attachDefaultActionInput']],
        ];
    }

    /**
     * @param FormRenderTagEvent $event
     */
    public function attachDefaultActionInput(FormRenderTagEvent $event)
    {
        if (!$event->getForm()->getHtmlAttributes()->get('action')) {
            $output = '<input type="hidden" name="action" value="express-forms/submit" />';

            $event->appendToOutput($output);
        }
    }
}
