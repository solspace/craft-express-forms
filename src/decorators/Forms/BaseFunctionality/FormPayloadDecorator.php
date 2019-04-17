<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\exceptions\Form\CorruptFormDataException;
use Solspace\ExpressForms\exceptions\Form\InsufficientFormDataPostedException;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Security\HashingInterface;

class FormPayloadDecorator extends AbstractDecorator
{
    const PAYLOAD_INPUT_NAME = 'formPayload';

    /** @var HashingInterface */
    private $hashing;

    /** @var LoggerProviderInterface */
    private $logger;

    public function __construct(HashingInterface $hashing, LoggerProviderInterface $logger)
    {
        $this->hashing = $hashing;
        $this->logger  = $logger;
    }

    public function getEventListenerList(): array
    {
        return [
            [Form::class, Form::EVENT_RENDER_CLOSING_TAG, [$this, 'attachPayloadToForm']],
            [SubmitController::class, SubmitController::EVENT_BEFORE_FORM_SUBMIT, [$this, 'parsePayload']],
        ];
    }

    /**
     * @param FormRenderTagEvent $event
     */
    public function attachPayloadToForm(FormRenderTagEvent $event)
    {
        $form = $event->getForm();

        $data = [
            'attributes' => $form->getHtmlAttributes()->toArray(),
            'parameters' => $form->getParameters()->toArray(),
        ];

        $serialized = \GuzzleHttp\json_encode($data);
        $encrypted  = $this->hashing->encrypt($serialized, $this->getSalt($form));

        $output = sprintf(
            '<input type="hidden" name="%s" value="%s" />',
            self::PAYLOAD_INPUT_NAME,
            $encrypted
        );

        $event->prependToOutput($output);
    }

    /**
     * @param FormSubmitEvent $event
     */
    public function parsePayload(FormSubmitEvent $event)
    {
        $submittedData = $event->getSubmittedData();
        $payload       = $submittedData[self::PAYLOAD_INPUT_NAME] ?? null;

        if (empty($payload)) {
            throw new InsufficientFormDataPostedException('Insufficient form data posted');
        }

        unset($submittedData[self::PAYLOAD_INPUT_NAME]);
        $event->setSubmittedData($submittedData);

        $form = $event->getForm();

        try {
            $decrypted = $this->hashing->decrypt($payload, $this->getSalt($form));
        } catch (\Exception $e) {
            throw new CorruptFormDataException('Corrupt form data posted');
        }

        try {
            $unserailzed = \GuzzleHttp\json_decode($decrypted, true);
        } catch (\Exception $exception) {
            $this->logger->get('Express Forms')->error($exception);
            $unserailzed = [];
        }

        $form->getHtmlAttributes()->merge($unserailzed['attributes'] ?? []);
        $form->getParameters()->merge($unserailzed['parameters'] ?? []);
    }

    /**
     * @param Form $form
     *
     * @return string
     */
    private function getSalt(Form $form): string
    {
        return $form->getUuid();
    }
}
