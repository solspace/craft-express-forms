<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\models\Form;

class CsrfFormDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_RENDER_OPENING_TAG, [$this, 'attachCsrfTokenToForm']],
        ];
    }

    public function attachCsrfTokenToForm(FormRenderTagEvent $event): void
    {
        if (\Craft::$app->config->general->enableCsrfProtection) {
            $csrfTokenName = \Craft::$app->config->general->csrfTokenName;
            $csrfToken = \Craft::$app->request->getCsrfToken();

            $event->appendToOutput('<input type="hidden" name="'.$csrfTokenName.'" value="'.$csrfToken.'" />');
        }
    }
}
