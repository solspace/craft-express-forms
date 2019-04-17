<?php

namespace Solspace\ExpressForms\providers\Logging;

use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;

class LoggerProvider implements LoggerProviderInterface
{
    /**
     * @param string $category
     *
     * @return LoggerInterface
     */
    public function get(string $category): LoggerInterface
    {
        return ExpressFormsLogger::getInstance($category);
    }
}
