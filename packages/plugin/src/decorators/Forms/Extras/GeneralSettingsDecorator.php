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
    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
        ];
    }

    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event): void
    {
        $event->addItem('General');
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('general' !== $event->getSelectedItem()) {
            return;
        }

        $event->setTitle('General Settings');
        $event->addContent(
            \Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/general',
                ['settings' => $event->getSettings()]
            )
        );
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('general');

        if (!empty($post) && \is_array($post)) {
            $event->addData('name', $post['name'] ?? null);
            $event->addData('enhancedUI', $post['enhancedUI'] ?? true);
        }
    }
}
