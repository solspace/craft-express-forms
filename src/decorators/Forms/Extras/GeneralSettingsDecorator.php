<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\services\Settings;

class GeneralSettingsDecorator extends AbstractDecorator
{
    /**
     * @return array
     */
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
        $event->addItem('General');
    }

    /**
     * @param RenderSettingsEvent $event
     */
    public function renderSettings(RenderSettingsEvent $event)
    {
        if ($event->getSelectedItem() !== 'general') {
            return;
        }

        $event->setTitle('General');
        $event->addContent(
            \Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/general',
                ['settings' => $event->getSettings()]
            )
        );
    }

    /**
     * @param SaveSettingsEvent $event
     */
    public function storeSettings(SaveSettingsEvent $event)
    {
        $post = Craft::$app->getRequest()->post('general');

        if (!empty($post) && is_array($post)) {
            $event->addData('name', $post['name'] ?? null);
            $event->addData('enhancedUI', $post['enhancedUI'] ?? true);
        }
    }
}
