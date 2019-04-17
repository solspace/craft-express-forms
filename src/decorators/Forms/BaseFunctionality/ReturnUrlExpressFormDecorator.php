<?php

namespace Solspace\ExpressForms\decorators\Forms\BaseFunctionality;

use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\integrations\types\MailChimp;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Security\HashingInterface;
use Solspace\ExpressForms\providers\View\RenderProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;

class ReturnUrlExpressFormDecorator extends AbstractDecorator
{
    const RETURN_URL_KEY = 'return';

    /** @var HashingInterface */
    private $hashing;

    /** @var RequestProviderInterface */
    private $request;

    /** @var RenderProviderInterface */
    private $renderer;

    /** @var LoggerProviderInterface */
    private $logger;

    /**
     * ReturnUrlExpressFormDecorator constructor.
     *
     * @param HashingInterface         $hashing
     * @param RequestProviderInterface $request
     * @param RenderProviderInterface  $renderer
     * @param LoggerProviderInterface  $logger
     */
    public function __construct(
        HashingInterface $hashing,
        RequestProviderInterface $request,
        RenderProviderInterface $renderer,
        LoggerProviderInterface $logger
    ) {
        $this->hashing  = $hashing;
        $this->request  = $request;
        $this->renderer = $renderer;
        $this->logger   = $logger;
    }

    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_REDIRECT, [$this, 'redirectPageAfterSubmit']],
        ];
    }

    /**
     * @param FormRenderTagEvent $event
     */
    public function attachReturnUrlToFormTag(FormRenderTagEvent $event)
    {
        $returnUrl = $event->getForm()->getParameters()->get(self::RETURN_URL_KEY);
        if ($returnUrl) {
            $returnUrl = $this->hashing->encrypt($returnUrl, $event->getForm()->getUuid());

            $output = sprintf(
                '<input type="hidden" name="return" value="%s" />',
                $returnUrl
            );

            $event->appendToOutput($output);
        }
    }

    /**
     * @param FormRedirectEvent $event
     */
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
                        'form'       => $event->getForm(),
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
                            'form'       => $event->getForm(),
                        ]
                    );

                $returnUrl = $_SERVER['HTTP_REFERER'] ?? '';
            }

            $event->setRedirectUrl($returnUrl);
        }
    }

    /**
     * @param RegisterIntegrationTypes $event
     */
    public function registerIntegrationTypes(RegisterIntegrationTypes $event)
    {
        $event->addType(MailChimp::class);
    }
}
