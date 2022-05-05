<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\resources\bundles\Settings\EmailNotificationsIndexBundle;
use Solspace\ExpressForms\services\Settings;
use Solspace\ExpressForms\utilities\Path;

class EmailNotificationsDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        $emailNotificationsService = ExpressForms::getInstance()->emailNotifications;

        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingsSidebarItem']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerUrl']],
            [
                SubmitController::class,
                SubmitController::EVENT_FORM_COMPLETED,
                [$emailNotificationsService, 'sendAdminNotifications'],
            ],
            [
                SubmitController::class,
                SubmitController::EVENT_FORM_COMPLETED,
                [$emailNotificationsService, 'sendEmailNotifications'],
            ],
        ];
    }

    public function registerSettingsSidebarItem(RegisterSettingSidebarItemsEvent $event): void
    {
        $event->addItem('Email Notifications');
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('email-notifications' !== $event->getSelectedItem()) {
            return;
        }

        EmailNotificationsIndexBundle::register(Craft::$app->getView());
        $emailNotificationsPath = ExpressForms::getInstance()->getSettings()->getEmailNotificationsPath();

        $event->setTitle('Email Notifications');
        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/email-notifications/index',
                [
                    'settings' => $event->getSettings(),
                    'notifications' => ExpressForms::getInstance()->emailNotifications->getNotifications(),
                    'path' => $emailNotificationsPath,
                ]
            )
        );

        if ($emailNotificationsPath) {
            $actionButton = Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/email-notifications/action-button'
            );
        } else {
            $actionButton = '';
        }

        $event->setActionButton($actionButton);
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('emailNotifications');

        if (!empty($post) && \is_array($post)) {
            if (empty($post['directoryPath'])) {
                $directoryPath = null;
            } else {
                $directoryPath = Path::getAbsoluteTemplatesPath($post['directoryPath']);
            }

            $event->addData('emailNotificationsDirectoryPath', $post['directoryPath'] ?? null);
            if (!empty($directoryPath) && (!file_exists($directoryPath) || !is_dir($directoryPath))) {
                $event->isValid = false;
                $event->addError(
                    'emailNotificationsDirectoryPath',
                    ExpressForms::t('Folder does not exist.')
                );
            }
        }
    }

    public function registerUrl(RegisterUrlRulesEvent $event): void
    {
        $rule = 'express-forms/settings/email-notifications/<fileName:(?:[^\/]*)>';
        $url = 'express-forms/email-notifications/edit';

        $event->rules['express-forms/email-notifications/save'] = 'express-forms/email-notifications/save';
        $event->rules['express-forms/settings/email-notifications/new'] = 'express-forms/email-notifications/create';
        $event->rules['express-forms/settings/email-notifications/delete'] = 'express-forms/email-notifications/delete';
        $event->rules[$rule] = $url;
    }
}
