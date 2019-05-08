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
    const AJAX_KEY           = 'duplicateCheck';
    const PREFIX             = 'fdchk-';
    const TTL                = 60 * 60 * 3; // 3 hours
    const MAX_SESSION_TOKENS = 40;

    /** @var SessionProviderInterface */
    private $session;

    /** @var HashingInterface */
    private $hashing;

    /** @var SettingsProviderInterface */
    private $settings;

    /**
     * PreventDuplicateSubmissionsDecorator constructor.
     *
     * @param SessionProviderInterface  $session
     * @param HashingInterface          $hashing
     * @param SettingsProviderInterface $settings
     */
    public function __construct(
        SessionProviderInterface $session,
        HashingInterface $hashing,
        SettingsProviderInterface $settings
    ) {
        $this->session  = $session;
        $this->hashing  = $hashing;
        $this->settings = $settings;
    }

    /**
     * @return array
     */
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

    /**
     * @param RenderSettingsEvent $event
     */
    public function renderSettings(RenderSettingsEvent $event)
    {
        if ($event->getSelectedItem() !== 'general') {
            return;
        }

        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/general/duplicates',
                ['settings' => $event->getSettings()]
            )
        );
    }

    /**
     * @param SaveSettingsEvent $event
     */
    public function storeSettings(SaveSettingsEvent $event)
    {
        $post = Craft::$app->getRequest()->post('duplicatePrevention');

        if (!empty($post) && is_array($post)) {
            $event->addData('duplicatePreventionEnabled', $post['enabled'] ?? true);
        }
    }

    /**
     * @param FormRenderTagEvent $event
     */
    public function attachInput(FormRenderTagEvent $event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $value  = $this->generateHash();
        $output = sprintf('<input type="hidden" name="%s" value="%s" />', $value, $value);

        $this->appendToSession($value);

        $event->prependToOutput($output);
    }

    /**
     * @param FormAjaxResponseEvent $event
     */
    public function attachToAjax(FormAjaxResponseEvent $event)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $value = $this->generateHash();
        $this->appendToSession($value);

        $event->addAjaxResponseData(
            self::AJAX_KEY,
            [
                'value'  => $value,
                'prefix' => $this->getPrefix(),
            ]
        );
    }

    /**
     * @param FormValidateEvent $event
     */
    public function validate(FormValidateEvent $event)
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

    private function cleanup()
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

    /**
     * @return string
     */
    private function getPrefix(): string
    {
        return self::PREFIX;
    }

    /**
     * @return int
     */
    private function getTTL(): int
    {
        return self::TTL;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateHash(): string
    {
        return $this->getPrefix() . $this->hashing->getUuid4();
    }

    /**
     * @param $key
     *
     * @return false|int
     */
    private function isHashedToken($key)
    {
        return preg_match("/^{$this->getPrefix()}/", $key);
    }

    /**
     * @param string $value
     */
    private function appendToSession(string $value)
    {
        $sortedByTime = [];
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $ttl) {
                if ($this->isHashedToken($key)) {
                    $sortedByTime[$ttl] = $key;
                }
            }
        }

        ksort($sortedByTime, SORT_DESC);
        if (count($sortedByTime) > self::MAX_SESSION_TOKENS) {
            while (count($sortedByTime) > self::MAX_SESSION_TOKENS) {
                $key = array_pop($sortedByTime);

                $this->session->remove($key);
            }
        }

        $this->session->set($value, time());
    }

    /**
     * @return bool
     */
    private function isEnabled(): bool
    {
        return (bool) $this->settings->get()->duplicatePreventionEnabled;
    }
}
