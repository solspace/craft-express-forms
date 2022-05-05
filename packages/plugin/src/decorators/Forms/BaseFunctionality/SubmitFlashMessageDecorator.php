<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormBuildFromArrayEvent;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Session\FlashBagProviderInterface;

class SubmitFlashMessageDecorator extends AbstractDecorator
{
    public const FORM_SUCCESSFUL_SUBMIT_KEY = 'submittedSuccessfully';

    public function __construct(private FlashBagProviderInterface $flashBag)
    {
    }

    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_FORM_COMPLETED, [$this, 'setFlashVariable']],
            [FormFactory::class, FormFactory::EVENT_AFTER_BUILD_FROM_ARRAY, [$this, 'attachParameterToForm']],
        ];
    }

    public function attachParameterToForm(FormBuildFromArrayEvent $event): void
    {
        try {
            $isSubmittedSuccessfully = $this->flashBag->get($this->getFlashBagKey($event->getForm()), false);
        } catch (\Exception $exception) {
            $isSubmittedSuccessfully = false;
        }

        $event->getForm()->getExtraParameters()->add(self::FORM_SUCCESSFUL_SUBMIT_KEY, $isSubmittedSuccessfully);
    }

    public function setFlashVariable(FormCompletedEvent $event): void
    {
        $this->flashBag->set($this->getFlashBagKey($event->getForm()), true);
    }

    private function getFlashBagKey(Form $form): string
    {
        return 'form-submitted-successfully-'.$form->getUuid();
    }
}
