<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use craft\helpers\StringHelper;
use Ramsey\Uuid\Uuid;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\events\forms\FormRenderTagEvent;
use Solspace\ExpressForms\events\forms\FormValidateEvent;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\providers\Plugin\SettingsProviderInterface;
use Solspace\ExpressForms\providers\View\RequestProviderInterface;
use Solspace\ExpressForms\services\Honeypot;
use Solspace\ExpressForms\services\Settings;

class HoneypotDecorator extends AbstractDecorator
{
    /** @var RequestProviderInterface */
    private $request;

    /** @var SettingsProviderInterface */
    private $settings;

    /**
     * HoneypotDecorator constructor.
     */
    public function __construct(RequestProviderInterface $request, SettingsProviderInterface $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
            [Form::class, Form::EVENT_RENDER_OPENING_TAG, [$this, 'attachHoneypotToFormTag']],
            [Form::class, Form::EVENT_VALIDATE_FORM, [$this, 'validateHoneypot']],
            [SubmitController::class, SubmitController::EVENT_REDIRECT, [$this, 'redirectForm']],
        ];
    }

    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event)
    {
        $event->addItem('Spam');
    }

    public function renderSettings(RenderSettingsEvent $event)
    {
        if ('spam' !== $event->getSelectedItem()) {
            return;
        }

        $event->setTitle('Spam Protection');
        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/spam/honeypot',
                ['settings' => $event->getSettings()]
            )
        );
    }

    public function storeSettings(SaveSettingsEvent $event)
    {
        $post = Craft::$app->getRequest()->post('honeypot');

        if (!empty($post) && \is_array($post)) {
            $name = $post['name'] ?? Honeypot::DEFAULT_NAME;
            $name = StringHelper::toKebabCase($name, '_');
            $name = StringHelper::toAscii($name);

            $event->addData('honeypotEnabled', $post['enabled'] ?? false);
            $event->addData('honeypotBehaviour', $post['behaviour'] ?? Honeypot::BEHAVIOUR_SIMULATE_SUCCESS);
            $event->addData('honeypotInputName', $name);
        }
    }

    public function attachHoneypotToFormTag(FormRenderTagEvent $event)
    {
        $settings = $this->settings->get();
        if (!$settings->honeypotEnabled) {
            return;
        }

        $id = Uuid::uuid4()->toString();

        $output = '';
        $output .= '<div style="position: fixed; left: -100%; top: -100%;" tabindex="-1" aria-hidden="true">';
        $output .= '<label for="'.$id.'" aria-hidden="true" tabindex="-1">Leave this alone</label>';
        $output .= '<input type="text"';
        $output .= ' name="'.($settings->honeypotInputName ?? Honeypot::DEFAULT_NAME).'"';
        $output .= ' value=""';
        $output .= ' id="'.$id.'"';
        $output .= ' tabindex="-1"';
        $output .= ' aria-hidden="true"';
        $output .= ' />';
        $output .= '</div>';

        $event->appendToOutput($output);
    }

    public function validateHoneypot(FormValidateEvent $event)
    {
        $settings = $this->settings->get();
        if (!$settings->honeypotEnabled) {
            return;
        }

        $honeypotName = $settings->honeypotInputName;
        $behaviour = $settings->honeypotBehaviour;

        $form = $event->getForm();

        if (!empty($this->request->post($honeypotName))) {
            $form->markAsSpam();

            if (Honeypot::BEHAVIOUR_SHOW_ERRORS === $behaviour) {
                $form->addError('Form has triggered spam control');
            }
        }
    }

    public function redirectForm(FormRedirectEvent $event)
    {
        $settings = $this->settings->get();
        if (!$settings->honeypotEnabled || Honeypot::BEHAVIOUR_RELOAD_FORM !== $settings->honeypotBehaviour) {
            return;
        }

        $honeypotName = $settings->honeypotInputName;
        if (!empty($this->request->post($honeypotName))) {
            $event->setRedirectUrl($_SERVER['HTTP_REFERER'] ?? '');
        }
    }
}
