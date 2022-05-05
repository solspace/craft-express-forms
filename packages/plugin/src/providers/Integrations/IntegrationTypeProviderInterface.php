<?php

namespace Solspace\ExpressForms\providers\Integrations;

use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

interface IntegrationTypeProviderInterface
{
    /**
     * @return IntegrationTypeInterface[]
     */
    public function getIntegrationTypes(): array;

    public function getIntegrationByClass(string $class): ?IntegrationTypeInterface;
}
