<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\integrations\types\MailChimp;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\View\RenderProviderInterface;

class ReturnUrlExpressFormDecorator extends AbstractDecorator
{
    const RETURN_URL_KEY = 'return';

    /** @var RenderProviderInterface */
    private $renderer;

    /** @var LoggerProviderInterface */
    private $logger;

    /**
     * ReturnUrlExpressFormDecorator constructor.
     */
    public function __construct(
        RenderProviderInterface $renderer,
        LoggerProviderInterface $logger
    ) {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }

    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_REDIRECT, [$this, 'redirectPageAfterSubmit']],
        ];
    }

    public function redirectPageAfterSubmit(FormRedirectEvent $event)
    {
        $returnUrl = $event->getForm()->getParameters()->get(self::RETURN_URL_KEY);
        if ($returnUrl) {
            try {
                $returnUrl = $this->renderer->renderObjectTemplate(
                    $returnUrl,
                    $event->getSubmission(),
                    [
                        'submission' => $event,
                        'form' => $event->getForm(),
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->get(ExpressFormsLogger::EXPRESS_FORMS)
                    ->error(
                        sprintf(
                            'Could not generate an URL for "%s" with the given Submission and Form settings',
                            $returnUrl
                        ),
                        [
                            'submission' => $event->getSubmission(),
                            'form' => $event->getForm(),
                        ]
                    )
                ;

                $returnUrl = $_SERVER['HTTP_REFERER'] ?? '';
            }

            $event->setRedirectUrl($returnUrl);
        }
    }

    public function registerIntegrationTypes(RegisterIntegrationTypes $event)
    {
        $event->addType(MailChimp::class);
    }
}
