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
    const FORM_SUCCESSFUL_SUBMIT_KEY = 'submittedSuccessfully';

    /** @var FlashBagProviderInterface */
    private $flashBag;

    /**
     * SubmitFlashMessageDecorator constructor.
     *
     * @param FlashBagProviderInterface $flashBag
     */
    public function __construct(FlashBagProviderInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * @return array
     */
    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_FORM_COMPLETED, [$this, 'setFlashVariable']],
            [FormFactory::class, FormFactory::EVENT_AFTER_BUILD_FROM_ARRAY, [$this, 'attachParameterToForm']],
        ];
    }

    /**
     * @param FormBuildFromArrayEvent $event
     */
    public function attachParameterToForm(FormBuildFromArrayEvent $event)
    {
        $isSubmittedSuccessfully = $this->flashBag->get($this->getFlashBagKey($event->getForm()), false);

        $event->getForm()->getExtraParameters()->add(self::FORM_SUCCESSFUL_SUBMIT_KEY, $isSubmittedSuccessfully);
    }

    /**
     * @param FormCompletedEvent $event
     */
    public function setFlashVariable(FormCompletedEvent $event)
    {
        $this->flashBag->set($this->getFlashBagKey($event->getForm()), true);
    }

    /**
     * @param Form $form
     *
     * @return string
     */
    private function getFlashBagKey(Form $form): string
    {
        return 'form-submitted-successfully-' . $form->getUuid();
    }
}
