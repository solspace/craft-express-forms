<?php

namespace Solspace\ExpressForms\providers\Integrations;

use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

interface IntegrationTypeProviderInterface
{
    /**
     * @return IntegrationTypeInterface[]
     */
    public function getIntegrationTypes(): array;

    /**
     * @param string $class
     *
     * @return null|IntegrationTypeInterface
     */
    public function getIntegrationByClass(string $class);
}
