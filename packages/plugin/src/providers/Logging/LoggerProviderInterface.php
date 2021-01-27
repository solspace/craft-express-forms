<?php

namespace Solspace\ExpressForms\providers\Logging;

use Psr\Log\LoggerInterface;

interface LoggerProviderInterface
{
    public function get(string $category): LoggerInterface;
}
