<?php

namespace Solspace\ExpressForms\services;

use craft\base\Component;
use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;

abstract class BaseService extends Component
{
    public function getFormsService(): Forms
    {
        return ExpressForms::getInstance()->forms;
    }

    public function getFieldsService(): Fields
    {
        return ExpressForms::getInstance()->fields;
    }

    public function getIntegrationsService(): Integrations
    {
        return ExpressForms::getInstance()->integrations;
    }

    public function getSettingsService(): Settings
    {
        return ExpressForms::getInstance()->settings;
    }

    public function getLogger(string $category = ExpressFormsLogger::EXPRESS_FORMS): LoggerInterface
    {
        return ExpressFormsLogger::getInstance($category);
    }
}
