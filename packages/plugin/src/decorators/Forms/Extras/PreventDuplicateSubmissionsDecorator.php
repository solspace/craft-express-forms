<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormAjaxResponseEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormValidateEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\Security\HashingInterface;
use Solspace\ExpressForms\providers\Session\SessionProviderInterface;
use Solspace\ExpressForms\services\Settings;

class PreventDuplicateSubmissionsDecorator extends AbstractDecorator
{
    public const AJAX_KEY = 'duplicateCheck';
    public const PREFIX = 'fdchk-';
    public const TTL = 60 * 60 * 3; // 3 hours
    public const MAX_SESSION_TOKENS = 40;

    public function __construct(
        private SessionProviderInterface $session,
        private HashingInterface $hashing,
        private SettingsProviderInterface $settings
    ) {
    }

    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
            [Form::class, Form::EVENT_RENDER_CLOSING_TAG, [$this, 'attachInput']],
            [Form::class, Form::EVENT_VALIDATE_FORM, [$this, 'validate']],
            [SubmitController::class, SubmitController::EVENT_BEFORE_AJAX_RESPONSE, [$this, 'attachToAjax']],
            [SubmitController::class, SubmitController::EVENT_BEFORE_AJAX_ERROR_RESPONSE, [$this, 'attachToAjax']],
        ];
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('general' !== $event->getSelectedItem()) {
            return;
        }

        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/general/duplicates',
                ['settings' => $event->getSettings()]
            )
        );
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('duplicatePrevention');

        if (!empty($post) && \is_array($post)) {
            $event->addData('duplicatePreventionEnabled', $post['enabled'] ?? true);
        }
    }

    public function attachInput(FormRenderTagEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $value = $this->generateHash();
        $output = sprintf('<input type="hidden" name="%s" value="%s" />', $value, $value);

        $this->appendToSession($value);

        $event->prependToOutput($output);
    }

    public function attachToAjax(FormAjaxResponseEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $value = $this->generateHash();
        $this->appendToSession($value);

        $event->addAjaxResponseData(
            self::AJAX_KEY,
            [
                'value' => $value,
                'prefix' => $this->getPrefix(),
            ]
        );
    }

    public function validate(FormValidateEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->cleanup();

        $hash = null;
        foreach ($event->getSubmittedData() as $key => $value) {
            if ($this->isHashedToken($key)) {
                $hash = $value;

                break;
            }
        }

        if (null === $hash || !$this->session->get($hash)) {
            $event->getForm()->setSkipped(true);

            return;
        }

        $this->session->remove($hash);
    }

    private function cleanup(): void
    {
        $currentTime = time();

        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if ($this->isHashedToken($key)) {
                    $diff = $currentTime - (int) $value;
                    if ($diff > $this->getTTL()) {
                        $this->session->remove($key);
                    }
                }
            }
        }
    }

    private function getPrefix(): string
    {
        return self::PREFIX;
    }

    private function getTTL(): int
    {
        return self::TTL;
    }

    private function generateHash(): string
    {
        return $this->getPrefix().$this->hashing->getUuid4();
    }

    private function isHashedToken($key): bool
    {
        return (bool) preg_match("/^{$this->getPrefix()}/", $key);
    }

    private function appendToSession(string $value): void
    {
        $sortedByTime = [];
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $ttl) {
                if ($this->isHashedToken($key)) {
                    $sortedByTime[$ttl] = $key;
                }
            }
        }

        ksort($sortedByTime, \SORT_DESC);
        if (\count($sortedByTime) > self::MAX_SESSION_TOKENS) {
            while (\count($sortedByTime) > self::MAX_SESSION_TOKENS) {
                $key = array_pop($sortedByTime);

                $this->session->remove($key);
            }
        }

        $this->session->set($value, time());
    }

    private function isEnabled(): bool
    {
        return (bool) $this->settings->get()->duplicatePreventionEnabled;
    }
}
