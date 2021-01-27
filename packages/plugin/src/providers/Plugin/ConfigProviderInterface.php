<?php

namespace Solspace\ExpressForms\providers\Plugin;

interface ConfigProviderInterface
{
    /**
     * Gets a configuration
     * Creates an empty file if none found.
     */
    public function getConfig(string $name): array;

    /**
     * Stores a configuration.
     */
    public function setConfig(string $name, array $configuration): bool;
}
