<?php

namespace Solspace\ExpressForms\providers\Integrations;

use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

class IntegrationTypeProvider implements IntegrationTypeProviderInterface
{
    public function getIntegrationTypes(): array
    {
        return ExpressForms::getInstance()->integrations->getIntegrationTypes();
    }

    public function getIntegrationByClass(string $class): ?IntegrationTypeInterface
    {
        return ExpressForms::getInstance()->integrations->getIntegrationByClass($class);
    }
}
