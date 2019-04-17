<?php

namespace Solspace\ExpressForms\providers\Integrations;

use Solspace\ExpressForms\ExpressForms;

class IntegrationTypeProvider implements IntegrationTypeProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getIntegrationTypes(): array
    {
        return ExpressForms::getInstance()->integrations->getIntegrationTypes();
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationByClass(string $class)
    {
        return ExpressForms::getInstance()->integrations->getIntegrationByClass($class);
    }
}
