<?php

namespace Solspace\ExpressForms\loggers;

use Psr\Log\LoggerInterface;
use Solspace\Commons\Loggers\LoggerFactory;

class ExpressFormsLogger
{
    public const EXPRESS_FORMS = 'Express Forms';
    public const FORM = 'Form';
    public const EMAIL_NOTIFICATION = 'Email Notification';
    public const INTEGRATIONS = 'Integrations';
    public const FILE_UPLOAD = 'File Upload';

    private static array $levelColorMap = [
        'DEBUG' => '#CCCCCC',
        'INFO' => '#6c757d',
        'NOTICE' => '#28a745',
        'WARNING' => '#ffc107',
        'ERROR' => '#dc3545',
        'CRITICAL' => '#dc3545',
        'ALERT' => '#dc3545',
        'EMERGENCY' => '#dc3545',
    ];

    /** @var LoggerInterface[] */
    private static array $loggers = [];

    public static function getInstance(string $category): LoggerInterface
    {
        if (!isset(self::$loggers[$category])) {
            self::$loggers[$category] = LoggerFactory::getOrCreateFileLogger($category, self::getLogfilePath());
        }

        return self::$loggers[$category];
    }

    public static function getLogfilePath(): string
    {
        return \Craft::$app->path->getLogPath().'/express-forms.log';
    }

    public static function getColor(string $level): string
    {
        return self::$levelColorMap[$level] ?? '#000000';
    }
}
