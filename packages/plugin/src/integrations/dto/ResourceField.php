<?php

namespace Solspace\ExpressForms\integrations\dto;

class ResourceField
{
    public function __construct(
        private string $name,
        private string $handle,
        private string $type,
        private bool $required = false,
        private array $settings = [],
        private ?string $category = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
