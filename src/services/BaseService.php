<?php

namespace Solspace\ExpressForms\services;

use craft\base\Component;
use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;

abstract class BaseService extends Component
{
    /**
     * @return Forms
     */
    public function getFormsService(): Forms
    {
        return ExpressForms::getInstance()->forms;
    }

    /**
     * @return Fields
     */
    public function getFieldsService(): Fields
    {
        return ExpressForms::getInstance()->fields;
    }

    /**
     * @return Integrations
     */
    public function getIntegrationsService(): Integrations
    {
        return ExpressForms::getInstance()->integrations;
    }

    /**
     * @return Settings
     */
    public function getSettingsService(): Settings
    {
        return ExpressForms::getInstance()->settings;
    }

    /**
     * @param string $category
     *
     * @return LoggerInterface
     */
    public function getLogger(string $category = ExpressFormsLogger::EXPRESS_FORMS): LoggerInterface
    {
        return ExpressFormsLogger::getInstance($category);
    }
}
