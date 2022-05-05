<?php

namespace Solspace\ExpressForms\integrations\dto;

use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

class Resource
{
    public function __construct(
        private IntegrationTypeInterface $type,
        private string $name,
        private string $handle,
        private array $settings = []
    ) {
    }

    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
