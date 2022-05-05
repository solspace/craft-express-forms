<?php

namespace Solspace\ExpressForms\integrations;

use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractIntegrationType implements IntegrationTypeInterface
{
    private static ?LoggerInterface $logger = null;

    private bool $markedForUpdate = false;

    public function __construct(array $settings = [])
    {
        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key) && $propertyAccess->isWritable($this, $key)) {
                $propertyAccess->setValue($this, $key, $value);
            }
        }
    }

    /**
     * Do something before settings are rendered.
     */
    public function beforeRenderUpdate(): void
    {
    }

    /**
     * Do something before settings are saved.
     */
    public function beforeSaveSettings(): void
    {
    }

    /**
     * Do something after settings are saved.
     */
    public function afterSaveSettings(): void
    {
    }

    public function isMarkedForUpdate(): bool
    {
        return $this->markedForUpdate;
    }

    public function markForUpdate(): self
    {
        $this->markedForUpdate = true;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'handle' => $this->getHandle(),
            'enabled' => $this->isEnabled(),
        ];
    }

    final public function getEndpoint(string $endpoint): string
    {
        $root = rtrim($this->getApiRootUrl(), '/');
        $endpoint = ltrim($endpoint, '/');

        return "{$root}/{$endpoint}";
    }

    abstract protected function getApiRootUrl(): string;

    protected function getLogger(): LoggerInterface
    {
        if (null === self::$logger) {
            self::$logger = ExpressFormsLogger::getInstance(ExpressFormsLogger::INTEGRATIONS);
        }

        return self::$logger;
    }
}
