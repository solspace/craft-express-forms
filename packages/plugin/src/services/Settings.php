<?php

namespace Solspace\ExpressForms\services;

use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Settings as SettingsModel;

class Settings extends BaseService
{
    const EVENT_BEFORE_SAVE_SETTINGS = 'beforeSaveSettings';
    const EVENT_AFTER_SAVE_SETTINGS = 'afterSaveSettings';
    const EVENT_REGISTER_SETTING_SIDEBAR_ITEMS = 'registerSettingSidebarItems';
    const EVENT_RENDER_SETTINGS = 'renderSettings';

    /** @var array */
    private $sidebarItems;

    public function getSettingsModel(): SettingsModel
    {
        return ExpressForms::getInstance()->getSettings();
    }

    public function getSidebarItems(): array
    {
        if (null === $this->sidebarItems) {
            $event = new RegisterSettingSidebarItemsEvent($this->getSettingsModel());

            $this->trigger(self::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, $event);

            $this->sidebarItems = $event->getSidebarItems();
        }

        return $this->sidebarItems;
    }

    public function saveSettings(): bool
    {
        $event = new SaveSettingsEvent();
        $this->trigger(self::EVENT_BEFORE_SAVE_SETTINGS, $event);

        if (!$event->isValid) {
            ExpressForms::getInstance()->getSettings()->addErrors($event->getErrors());

            return false;
        }

        $result = \Craft::$app->plugins->savePluginSettings(ExpressForms::getInstance(), $event->getData());
        $this->trigger(self::EVENT_AFTER_SAVE_SETTINGS);

        return $result;
    }

    public function onRenderSettings(string $selectedHandle): RenderSettingsEvent
    {
        $event = new RenderSettingsEvent($this->getSettingsModel(), $selectedHandle);
        $this->trigger(self::EVENT_RENDER_SETTINGS, $event);

        return $event;
    }
}
