<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\types\CampaignMonitor;
use Solspace\ExpressForms\integrations\types\ConstantContact;
use Solspace\ExpressForms\integrations\types\HubSpot;
use Solspace\ExpressForms\integrations\types\MailChimp;
use Solspace\ExpressForms\integrations\types\Salesforce;
use Solspace\ExpressForms\providers\Integrations\IntegrationTypeProviderInterface;
use Solspace\ExpressForms\resources\bundles\IntegrationsIndex;
use Solspace\ExpressForms\services\Integrations;
use Solspace\ExpressForms\services\Settings;
use yii\base\Event;

class IntegrationPreviewDecorator extends AbstractDecorator
{
    /** @var IntegrationTypeProviderInterface */
    private $integrationTypes;

    /**
     * IntegrationsDecorator constructor.
     *
     * @param IntegrationTypeProviderInterface $integrationTypes
     */
    public function __construct(IntegrationTypeProviderInterface $integrationTypes)
    {
        $this->integrationTypes = $integrationTypes;
    }

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
        $event->addItem('API Integrations');
    }

    /**
     * @param RenderSettingsEvent $event
     */
    public function renderSettings(RenderSettingsEvent $event)
    {
        if ($event->getSelectedItem() !== 'api-integrations' || ExpressForms::getInstance()->isPro()) {
            return;
        }

        $originalDecorator = ExpressForms::container()->get(IntegrationsDecorator::class);

        Event::on(
            Integrations::class,
            Integrations::EVENT_REGISTER_INTEGRATIONS,
            [$originalDecorator, 'registerIntegrationTypes']
        );

        IntegrationsIndex::register(Craft::$app->getView());

        $event
            ->setTitle('API Integrations')
            ->setActionButton('')
            ->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/integrations/preview',
                [
                    'integrations' => $this->integrationTypes->getIntegrationTypes(),
                ]
            )
        );
    }
}
