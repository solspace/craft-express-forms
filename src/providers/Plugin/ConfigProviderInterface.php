<?php

namespace Solspace\ExpressForms\providers\Plugin;

interface ConfigProviderInterface
{
    /**
     * Gets a configuration
     * Creates an empty file if none found
     *
     * @param string $name
     *
     * @return array
     */
    public function getConfig(string $name): array;

    /**
     * Stores a configuration
     *
     * @param string $name
     * @param array  $configuration
     *
     * @return bool
     */
    public function setConfig(string $name, array $configuration): bool;
}
