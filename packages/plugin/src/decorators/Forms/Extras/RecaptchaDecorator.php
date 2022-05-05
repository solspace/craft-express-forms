<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use GuzzleHttp\Client;
use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\decorators\AbstractTranslatableDecorator;
use Solspace\ExpressForms\events\forms\FormBuildFromArrayEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormValidateEvent;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\factories\FormFactory;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\models\Settings as SettingsModel;
use Solspace\ExpressForms\objects\Form\Recaptcha;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;
use Solspace\ExpressForms\services\Settings;

class RecaptchaDecorator extends AbstractTranslatableDecorator
{
    public const FORM_RECAPTCHA_KEY = 'recaptcha';

    public function __construct(
        private RequestProviderInterface $request,
        private SettingsProviderInterface $settings,
        TranslatorInterface $translator
    ) {
        parent::__construct($translator);
    }

    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
            [FormFactory::class, FormFactory::EVENT_AFTER_BUILD_FROM_ARRAY, [$this, 'attachRecaptchaToForm']],
            [Form::class, Form::EVENT_RENDER_CLOSING_TAG, [$this, 'addRecaptchaScript']],
            [Form::class, Form::EVENT_VALIDATE_FORM, [$this, 'validateRecaptcha']],
        ];
    }

    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event): void
    {
        $event->addItem('Spam');
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('spam' !== $event->getSelectedItem()) {
            return;
        }

        $event->setTitle('Spam Protection');
        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/spam/recaptcha',
                ['settings' => $event->getSettings()]
            )
        );
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('recaptcha');

        if (!empty($post) && \is_array($post)) {
            $event->addData('recaptchaEnabled', $post['enabled'] ?? false);
            $event->addData('recaptchaLoadScript', $post['loadScript'] ?? true);
            $event->addData('recaptchaSiteKey', $post['siteKey'] ?: null);
            $event->addData('recaptchaSecretKey', $post['secretKey'] ?: null);
            $event->addData('recaptchaTheme', $post['theme'] ?: null);
        }
    }

    public function attachRecaptchaToForm(FormBuildFromArrayEvent $event): void
    {
        $key = $this->isRecaptchaEnabled() ? $this->getSettings()->recaptchaSiteKey : null;
        $theme = $this->isRecaptchaEnabled() ? $this->getSettings()->recaptchaTheme : null;

        $event->getForm()->getExtraParameters()->add(self::FORM_RECAPTCHA_KEY, new Recaptcha($key, $theme));
    }

    public function addRecaptchaScript(FormRenderTagEvent $event): void
    {
        $settings = $this->getSettings();
        if ($settings->recaptchaEnabled && $settings->recaptchaLoadScript) {
            $form = $event->getForm();

            /** @var Recaptcha $recaptcha */
            $recaptcha = $form->getExtraParameters()->get(self::FORM_RECAPTCHA_KEY);
            if ($recaptcha && $recaptcha->isRendered()) {
                $form->getParameters()->add(self::FORM_RECAPTCHA_KEY, true);
                $event->appendToOutput('<script src="https://www.google.com/recaptcha/api.js" async defer></script>');
            }
        }
    }

    public function validateRecaptcha(FormValidateEvent $event): void
    {
        $settings = $this->getSettings();
        $form = $event->getForm();
        if (!$settings->recaptchaEnabled || !$form->getParameters()->get(self::FORM_RECAPTCHA_KEY, false)) {
            return;
        }

        /** @var Recaptcha $recaptcha */
        $recaptcha = $event->getForm()->getExtraParameters()->get(self::FORM_RECAPTCHA_KEY);

        $response = $this->request->post('g-recaptcha-response');
        $errorMessage = $this->translate('Please verify that you are not a robot.');

        if (!$response) {
            $recaptcha->addError($errorMessage);
            $event->getForm()
                ->setValid(false)
                ->addError($errorMessage)
            ;
        } else {
            $secret = $this->getSettings()->recaptchaSecretKey;

            $client = new Client();
            $response = $client->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'form_params' => [
                        'secret' => $secret,
                        'response' => $response,
                        'remoteip' => $this->request->getRemoteIP(),
                    ],
                ]
            );

            $result = json_decode((string) $response->getBody(), true);

            if (!$result['success']) {
                $recaptcha->addError($errorMessage);
                $event->getForm()
                    ->setValid(false)
                    ->addError($errorMessage)
                ;
            }
        }
    }

    private function isRecaptchaEnabled(): bool
    {
        return (bool) $this->getSettings()->recaptchaEnabled;
    }

    private function getSettings(): SettingsModel
    {
        return $this->settings->get();
    }
}
