<?php

namespace Solspace\ExpressForms\events\integrations;

use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use yii\base\Event;

class RegisterIntegrationTypes extends Event
{
    /** @var IntegrationTypeInterface[] */
    private $types = [];

    /** @var array */
    private $config;

    /**
     * RegisterIntegrationTypes constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    /**
     * @param string $class
     *
     * @return RegisterIntegrationTypes
     */
    public function addType(string $class): RegisterIntegrationTypes
    {
        $reflection = new \ReflectionClass($class);
        if ($reflection->implementsInterface(IntegrationTypeInterface::class)) {
            $this->types[] = new $class($this->config[$class] ?? []);
        }

        return $this;
    }

    /**
     * @return IntegrationTypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
