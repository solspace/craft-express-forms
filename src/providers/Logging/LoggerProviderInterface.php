<?php

namespace Solspace\ExpressForms\providers\Logging;

use Psr\Log\LoggerInterface;

interface LoggerProviderInterface
{
    /**
     * @param string $category
     *
     * @return LoggerInterface
     */
    public function get(string $category): LoggerInterface;
}
