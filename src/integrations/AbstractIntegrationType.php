<?php

namespace Solspace\ExpressForms\integrations;

use Psr\Log\LoggerInterface;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractIntegrationType implements IntegrationTypeInterface
{
    /** @var LoggerInterface */
    private static $logger;

    /** @var bool */
    private $markedForUpdate = false;

    /**
     * AbstractIntegrationType constructor.
     *
     * @param array $settings
     */
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
     * Do something before settings are rendered
     */
    public function beforeRenderUpdate()
    {
    }

    /**
     * Do something before settings are saved
     */
    public function beforeSaveSettings()
    {
    }

    /**
     * Do something after settings are saved
     */
    public function afterSaveSettings()
    {
    }

    /**
     * @return bool
     */
    public function isMarkedForUpdate(): bool
    {
        return $this->markedForUpdate;
    }

    /**
     * @return $this
     */
    public function markForUpdate(): AbstractIntegrationType
    {
        $this->markedForUpdate = true;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'name'    => $this->getName(),
            'handle'  => $this->getHandle(),
            'enabled' => $this->isEnabled(),
        ];
    }

    /**
     * @param string $endpoint
     *
     * @return string
     */
    final public function getEndpoint(string $endpoint): string
    {
        $root     = rtrim($this->getApiRootUrl(), '/');
        $endpoint = ltrim($endpoint, '/');

        return "$root/$endpoint";
    }

    /**
     * @return string
     */
    abstract protected function getApiRootUrl(): string;

    /**
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        if (null === self::$logger) {
            self::$logger = ExpressFormsLogger::getInstance(ExpressFormsLogger::INTEGRATIONS);
        }

        return self::$logger;
    }
}
