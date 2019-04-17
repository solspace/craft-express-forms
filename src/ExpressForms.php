<?php

namespace Solspace\ExpressForms;

use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Dashboard;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use Solspace\Commons\Events\RegisterCpSubnavItemsEvent;
use Solspace\ExpressForms\decorators\ExtraBundle;
use Solspace\ExpressForms\decorators\Fields\EmailFieldValidatorDecorator;
use Solspace\ExpressForms\decorators\Fields\FileUploadDecorator;
use Solspace\ExpressForms\decorators\Fields\RequiredFieldValidatorDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\CsrfFormDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\DefaultActionDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\FormIdDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\FormPayloadDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\ReturnUrlExpressFormDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\SkipSubmissionStorageDecorator;
use Solspace\ExpressForms\decorators\Forms\BaseFunctionality\SubmitFlashMessageDecorator;
use Solspace\ExpressForms\decorators\Forms\Export\CsvExporterDecorator;
use Solspace\ExpressForms\decorators\Forms\Export\ExcelExporterDecorator;
use Solspace\ExpressForms\decorators\Forms\Export\JsonExporterDecorator;
use Solspace\ExpressForms\decorators\Forms\Export\XmlExporterDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\CodePackDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\DynamicNotificationsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\DynamicRecipientsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\EmailNotificationsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\ErrorLogDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\GeneralSettingsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\HoneypotDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\IntegrationPreviewDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\IntegrationsDecorator;
use Solspace\ExpressForms\decorators\Forms\Extras\RecaptchaDecorator;
use Solspace\ExpressForms\models\Settings as SettingsModel;
use Solspace\ExpressForms\services\Container;
use Solspace\ExpressForms\services\EmailNotifications;
use Solspace\ExpressForms\services\Export;
use Solspace\ExpressForms\services\Fields;
use Solspace\ExpressForms\services\Forms;
use Solspace\ExpressForms\services\Integrations;
use Solspace\ExpressForms\services\Settings;
use Solspace\ExpressForms\services\Submissions;
use Solspace\ExpressForms\Services\Widgets;
use Solspace\ExpressForms\twig\filters\ClassFilter;
use Solspace\ExpressForms\variables\ExpressFormsVariable;
use Solspace\ExpressForms\widgets\OverviewStatsWidget;
use yii\base\Event;

/**
 * Class ExpressForms
 *
 * @package Solspace\ExpressForms
 *
 * @property Forms              $forms
 * @property Fields             $fields
 * @property Integrations       $integrations
 * @property Submissions        $submissions
 * @property Settings           $settings
 * @property Container          $container
 * @property EmailNotifications $emailNotifications
 * @property Export             $export
 * @property Widgets            $widgets
 */
class ExpressForms extends Plugin
{
    const TRANSLATION_CATEGORY = 'express-forms';

    const VIEW_FORMS       = 'dashboard';
    const VIEW_SUBMISSIONS = 'forms';
    const VIEW_SETTINGS    = 'settings';

    const EVENT_REGISTER_SUBNAV_ITEMS = 'registerSubnavItems';

    const PERMISSIONS_HELP_LINK = 'http://craft.express/forms/v2/';
    const PERMISSION_NAMESPACE  = 'Express Forms';

    const PERMISSION_SUBMISSIONS = 'express-forms-submissions';
    const PERMISSION_FORMS       = 'express-forms-forms';
    const PERMISSION_SETTINGS    = 'express-forms-settings';
    const PERMISSION_RESOURCES   = 'express-forms-resources';

    const EDITION_LITE = 'lite';
    const EDITION_PRO  = 'pro';

    private $decorators = [
        // Field decorators
        // -----------------
        RequiredFieldValidatorDecorator::class,
        EmailFieldValidatorDecorator::class,
        FileUploadDecorator::class,

        // Form decorators
        // ------------------

        // Base Functionality
        FormIdDecorator::class,
        DefaultActionDecorator::class,
        ReturnUrlExpressFormDecorator::class,
        CsrfFormDecorator::class,
        SkipSubmissionStorageDecorator::class,
        SubmitFlashMessageDecorator::class,

        // Extras
        GeneralSettingsDecorator::class,
        HoneypotDecorator::class,
        RecaptchaDecorator::class,
        FormPayloadDecorator::class,
        EmailNotificationsDecorator::class,
        IntegrationsDecorator::class,
        IntegrationPreviewDecorator::class,
        CodePackDecorator::class,
        DynamicNotificationsDecorator::class,
        DynamicRecipientsDecorator::class,
        ErrorLogDecorator::class,

        // Export
        CsvExporterDecorator::class,
        ExcelExporterDecorator::class,
        JsonExporterDecorator::class,
        XmlExporterDecorator::class,
    ];

    /** @var bool */
    public $hasCpSection = true;

    /** @var bool */
    public $hasCpSettings = true;

    /**
     * @return ExpressForms
     */
    public static function getInstance(): ExpressForms
    {
        return parent::getInstance();
    }

    /**
     * @return Container
     */
    public static function container(): Container
    {
        return self::getInstance()->container;
    }

    /**
     * @return array
     */
    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    /**
     * @param string $message
     * @param array  $params
     * @param string $language
     *
     * @return string
     */
    public static function t(string $message, array $params = [], string $language = null): string
    {
        return \Craft::t(self::TRANSLATION_CATEGORY, $message, $params, $language);
    }

    public function init()
    {
        parent::init();
        \Yii::setAlias('@expressforms', __DIR__);

        $this->initServices();
        $this->initRoutes();
        $this->initTwigVariables();
        $this->initPermissions();
        $this->initWidgets();
        $this->initDecorators();

        if ($this->isPro() && $this->getSettings()->name) {
            $this->name = $this->getSettings()->name;
        }
    }

    /**
     * @return bool
     */
    public function isPro(): bool
    {
        return $this->is(self::EDITION_PRO);
    }

    /**
     * @return bool
     */
    public function isLite(): bool
    {
        return $this->is(self::EDITION_LITE);
    }

    /**
     * @return array|null
     */
    public function getCpNavItem()
    {
        $navItem = parent::getCpNavItem();

        $subNavigation = include __DIR__ . '/subnav.php';
        $event         = new RegisterCpSubnavItemsEvent($subNavigation);
        $this->trigger(self::EVENT_REGISTER_SUBNAV_ITEMS, $event);

        $navItem['subnav'] = $event->getSubnavItems();

        return $navItem;
    }

    public function initServices()
    {
        $this->setComponents(
            [
                'forms'              => Forms::class,
                'fields'             => Fields::class,
                'integrations'       => Integrations::class,
                'submissions'        => Submissions::class,
                'settings'           => Settings::class,
                'container'          => Container::class,
                'emailNotifications' => EmailNotifications::class,
                'export'             => Export::class,
                'widgets'            => Widgets::class,
            ]
        );
    }

    /**
     * @return SettingsModel
     */
    public function getSettings(): SettingsModel
    {
        return parent::getSettings();
    }

    /**
     * @return SettingsModel
     */
    protected function createSettingsModel(): SettingsModel
    {
        return new SettingsModel();
    }

    /**
     * @return string
     */
    protected function settingsHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('express-forms/settings/_redirect');
    }

    /**
     * @return bool
     */
    protected function beforeUninstall(): bool
    {
        $forms = $this->forms->getAllForms();
        foreach ($forms as $form) {
            $this->forms->deleteById($form->getId());
        }

        return true;
    }

    private function initRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $routes       = include __DIR__ . '/routes.php';
                $event->rules = array_merge($event->rules, $routes);
            }
        );
    }

    private function initTwigVariables()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $event->sender->set('expressforms', ExpressFormsVariable::class);
            }
        );

        \Craft::$app->view->registerTwigExtension(new ClassFilter());
    }

    private function initPermissions()
    {
        if (\Craft::$app->getEdition() >= \Craft::Pro) {
            Event::on(
                UserPermissions::class,
                UserPermissions::EVENT_REGISTER_PERMISSIONS,
                function (RegisterUserPermissionsEvent $event) {
                    $permissions = [
                        self::PERMISSION_FORMS       => ['label' => self::t('Access & Manage Forms')],
                        self::PERMISSION_SUBMISSIONS => ['label' => self::t('Access & Export Submissions')],
                        self::PERMISSION_SETTINGS    => ['label' => self::t('Access & Manage Settings')],
                        self::PERMISSION_RESOURCES   => ['label' => self::t('Access Resources')],
                    ];

                    if (!isset($event->permissions[self::PERMISSION_NAMESPACE])) {
                        $event->permissions[self::PERMISSION_NAMESPACE] = [];
                    }

                    $event->permissions[self::PERMISSION_NAMESPACE] = array_merge(
                        $event->permissions[self::PERMISSION_NAMESPACE],
                        $permissions
                    );
                }
            );
        }
    }

    private function initWidgets()
    {
        if ($this->isPro()) {
            Event::on(
                Dashboard::class,
                Dashboard::EVENT_REGISTER_WIDGET_TYPES,
                static function (RegisterComponentTypesEvent $event) {
                    $event->types[] = OverviewStatsWidget::class;
                }
            );
        }
    }

    private function initDecorators()
    {
        foreach ($this->decorators as $decorator) {
            $reflection = new \ReflectionClass($decorator);
            if ($reflection->implementsInterface(ExtraBundle::class) && !$this->isPro()) {
                continue;
            }

            $this->container->getDecorator($decorator)->initEventListeners();
        }
    }
}
