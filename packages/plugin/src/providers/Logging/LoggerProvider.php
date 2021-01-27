<?php

namespace Solspace\ExpressForms\providers\Logging;

use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;

class LoggerProvider implements LoggerProviderInterface
{
    public function get(string $category): LoggerInterface
    {
        return ExpressFormsLogger::getInstance($category);
    }
}
