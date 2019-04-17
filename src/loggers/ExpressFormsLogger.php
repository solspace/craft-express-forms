<?php

namespace Solspace\ExpressForms\loggers;

use Psr\Log\LoggerInterface;
use Solspace\Commons\Loggers\LoggerFactory;

class ExpressFormsLogger
{
    const EXPRESS_FORMS      = 'Express Forms';
    const FORM               = 'Form';
    const EMAIL_NOTIFICATION = 'Email Notification';
    const INTEGRATIONS       = 'Integrations';
    const FILE_UPLOAD        = 'File Upload';

    private static $levelColorMap = [
        'DEBUG'     => '#CCCCCC',
        'INFO'      => '#6c757d',
        'NOTICE'    => '#28a745',
        'WARNING'   => '#ffc107',
        'ERROR'     => '#dc3545',
        'CRITICAL'  => '#dc3545',
        'ALERT'     => '#dc3545',
        'EMERGENCY' => '#dc3545',
    ];

    /** @var LoggerInterface[] */
    private static $loggers = [];

    /**
     * @param string $category
     *
     * @return LoggerInterface
     */
    public static function getInstance(string $category): LoggerInterface
    {
        if (!isset(self::$loggers[$category])) {
            self::$loggers[$category] = LoggerFactory::getOrCreateFileLogger($category, self::getLogfilePath());
        }

        return self::$loggers[$category];
    }

    /**
     * @return string
     */
    public static function getLogfilePath(): string
    {
        return \Craft::$app->path->getLogPath() . '/express-forms.log';
    }

    /**
     * @param string $level
     *
     * @return string
     */
    public static function getColor(string $level): string
    {
        return self::$levelColorMap[$level] ?? '#000000';
    }
}
