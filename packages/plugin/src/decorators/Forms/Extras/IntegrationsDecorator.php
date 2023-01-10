<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\decorators\ExtraBundle;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\events\settings\RegisterSettingSidebarItemsEvent;
use Solspace\ExpressForms\events\settings\RenderSettingsEvent;
use Solspace\ExpressForms\events\settings\SaveSettingsEvent;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\types\CampaignMonitor;
use Solspace\ExpressForms\integrations\types\ConstantContactV1;
use Solspace\ExpressForms\integrations\types\ConstantContactV3;
use Solspace\ExpressForms\integrations\types\HubSpot;
use Solspace\ExpressForms\integrations\types\HubSpotV1;
use Solspace\ExpressForms\integrations\types\MailChimp;
use Solspace\ExpressForms\integrations\types\Salesforce;
use Solspace\ExpressForms\objects\Integrations\Setting;
use Solspace\ExpressForms\providers\Integrations\IntegrationTypeProviderInterface;
use Solspace\ExpressForms\resources\bundles\IntegrationsIndex;
use Solspace\ExpressForms\services\Integrations;
use Solspace\ExpressForms\services\Settings;
use Symfony\Component\PropertyAccess\PropertyAccess;
use yii\web\NotFoundHttpException;

class IntegrationsDecorator extends AbstractDecorator implements ExtraBundle
{
    public function __construct(private IntegrationTypeProviderInterface $integrationTypes)
    {
    }

    public function getEventListenerList(): array
    {
        return [
            [Settings::class, Settings::EVENT_REGISTER_SETTING_SIDEBAR_ITEMS, [$this, 'registerSettingItems']],
            [Settings::class, Settings::EVENT_RENDER_SETTINGS, [$this, 'renderSettings']],
            [Settings::class, Settings::EVENT_BEFORE_SAVE_SETTINGS, [$this, 'storeSettings']],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerUrl']],
            [Integrations::class, Integrations::EVENT_REGISTER_INTEGRATIONS, [$this, 'registerIntegrationTypes']],
            [SubmitController::class, SubmitController::EVENT_FORM_COMPLETED, [$this, 'pushData']],
        ];
    }

    public function registerSettingItems(RegisterSettingSidebarItemsEvent $event): void
    {
        $event->addItem('API Integrations');
    }

    public function registerIntegrationTypes(RegisterIntegrationTypes $event): void
    {
        $event
            ->addType(CampaignMonitor::class)
            ->addType(ConstantContactV1::class)
            ->addType(ConstantContactV3::class)
            ->addType(HubSpot::class)
            ->addType(HubSpotV1::class)
            ->addType(MailChimp::class)
            ->addType(Salesforce::class)
        ;
    }

    public function renderSettings(RenderSettingsEvent $event): void
    {
        if ('api-integrations' !== $event->getSelectedItem()) {
            return;
        }

        $event->setTitle('API Integrations');

        IntegrationsIndex::register(Craft::$app->getView());

        $integrationTypeHandle = Craft::$app->getRequest()->getSegment(4);
        if ($integrationTypeHandle) {
            $integrationType = ExpressForms::getInstance()->integrations->getIntegrationByHandle(
                $integrationTypeHandle
            );

            if (!$integrationType) {
                throw new NotFoundHttpException(
                    ExpressForms::t('Could not find integration')
                );
            }

            $integrationType->beforeRenderUpdate();
            if ($integrationType->isMarkedForUpdate()) {
                ExpressForms::getInstance()->integrations->storeConfig($integrationType);
                header('Location: '.UrlHelper::cpUrl('express-forms/settings/api-integrations'));

                exit;
            }

            $event->addContent(
                Craft::$app->getView()->renderTemplate(
                    'express-forms/settings/_components/integrations/edit',
                    [
                        'integrationType' => $integrationType,
                        'settings' => ExpressForms::getInstance()->getSettings(),
                    ]
                )
            );

            return;
        }

        $event->setActionButton('');
        $event->addContent(
            Craft::$app->getView()->renderTemplate(
                'express-forms/settings/_components/integrations',
                [
                    'integrations' => $this->integrationTypes->getIntegrationTypes(),
                ]
            )
        );
    }

    public function storeSettings(SaveSettingsEvent $event): void
    {
        $post = Craft::$app->getRequest()->post('integrations');

        if (!empty($post) && \is_array($post)) {
            $class = $post['class'] ?? null;
            $integrationType = ExpressForms::getInstance()->integrations->getIntegrationByClass($class);

            if (!$integrationType) {
                throw new NotFoundHttpException('Could not find integration');
            }

            $propertyAccess = PropertyAccess::createPropertyAccessor();
            foreach ($integrationType::getSettingsManifest() as $setting) {
                $value = $post[$setting->getHandle()] ?? null;
                if (empty($value) && $setting->isRequired()) {
                    $event->addError($setting->getHandle(), ExpressForms::t('This field is required'));
                }

                if (Setting::TYPE_BOOLEAN === $setting->getType()) {
                    $value = (bool) $value;
                }

                $propertyAccess->setValue($integrationType, $setting->getHandle(), $value);
            }

            if (\count($event->getErrors())) {
                $event->isValid = false;

                return;
            }

            $integrationType->beforeSaveSettings();

            ExpressForms::getInstance()->integrations->storeConfig($integrationType);

            $integrationType->afterSaveSettings();

            if ($integrationType->isEnabled()) {
                try {
                    ExpressForms::getInstance()->integrations->fetchData($integrationType);
                } catch (\Throwable $e) {
                }
            }
        }
    }

    public function registerUrl(RegisterUrlRulesEvent $event): void
    {
        $editRule = 'express-forms/settings/api-integrations/<handle:(?:[^\/]*)>';
        $event->rules[$editRule] = 'express-forms/settings/index';

        $refreshRule = 'express-forms/integrations/refresh-resources';
        $event->rules[$refreshRule] = 'express-forms/integrations/refresh-resources';

        $queryRule = 'express-forms/integrations/query-integration';
        $event->rules[$queryRule] = $queryRule;
    }

    public function pushData(FormCompletedEvent $event): void
    {
        $form = $event->getForm();
        if ($form->isMarkedAsSpam() || $form->isSkipped()) {
            return;
        }

        /** @var IntegrationMappingInterface $integration */
        foreach ($event->getForm()->getIntegrations() as $integration) {
            try {
                $integration->pushData($event->getPostData());
            } catch (ConnectionFailedException $e) {
            }

            if ($integration->getType()->isMarkedForUpdate()) {
                ExpressForms::getInstance()->integrations->storeConfig($integration->getType());
            }
        }
    }
}
