<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use Solspace\Commons\Loggers\Readers\LineLogReader;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\services\Settings;

class ErrorLogDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
        ];
    }

    /**
     * @param RegisterSettingSidebarItemsEvent $event
     */
    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event)
    {
        $count = $this->getLogReader()->count();
        $event->addItem('General', 'general', 0);
        $event->addItem("Error Log ($count)", 'error-log');
    }

    /**
     * @param RenderSettingsEvent $event
     */
    public function renderSettings(RenderSettingsEvent $event)
    {
        if ($event->getSelectedItem() === 'error-log') {
            $event->setAllowViewingWithoutAdminChanges(true);
            $event
                ->setTitle('Error Log')
                ->setActionButton(
                    \Craft::$app->getView()->renderTemplate(
                        'express-forms/settings/_components/error-log/action-button'
                    )
                )
                ->addContent(
                    \Craft::$app->getView()->renderTemplate(
                        'express-forms/settings/_components/error-log',
                        ['logReader' => $this->getLogReader()]
                    )
                );
        }

        if ($event->getSelectedItem() === 'general') {
            $event
                ->addContent(
                    \Craft::$app->getView()->renderTemplate(
                        'express-forms/settings/_components/error-log/settings',
                        ['settings' => $event->getSettings()]
                    )
                );
        }
    }

    /**
     * @param SaveSettingsEvent $event
     */
    public function storeSettings(SaveSettingsEvent $event)
    {
        $post = Craft::$app->getRequest()->post('general');

        if (!empty($post) && is_array($post)) {
            $event->addData('showErrorLogBanner', $post['showErrorLogBanner'] ?? true);
        }
    }

    /**
     * @return LineLogReader
     */
    public function getLogReader(): LineLogReader
    {
        return new LineLogReader(ExpressFormsLogger::getLogfilePath());
    }
}
