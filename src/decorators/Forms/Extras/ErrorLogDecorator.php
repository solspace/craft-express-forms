<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Solspace\Commons\Loggers\Readers\LineLogReader;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\services\Settings;

class ErrorLogDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
        ];
    }

    /**
     * @param RegisterSettingSidebarItemsEvent $event
     */
    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event)
    {
        $count = $this->getLogReader()->count();
        $event->addItem("Error Log ($count)", 'error-log');
    }

    /**
     * @param RenderSettingsEvent $event
     */
    public function renderSettings(RenderSettingsEvent $event)
    {
        if ($event->getSelectedItem() !== 'error-log') {
            return;
        }


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

    /**
     * @return LineLogReader
     */
    public function getLogReader(): LineLogReader
    {
        return new LineLogReader(ExpressFormsLogger::getLogfilePath());
    }
}
