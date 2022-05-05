<?php

namespace Solspace\ExpressForms\objects\Integrations;

class Setting
{
    public const TYPE_TEXT = 'text';
    public const TYPE_BOOLEAN = 'boolean';

    public function __construct(
        private string $label,
        private string $handle,
        private ?string $description = null,
        private string $type = self::TYPE_TEXT,
        private bool $required = false
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
