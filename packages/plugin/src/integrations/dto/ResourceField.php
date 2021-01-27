<?php

namespace Solspace\ExpressForms\integrations\dto;

class ResourceField
{
    /** @var string */
    private $name;

    /** @var string */
    private $handle;

    /** @var string */
    private $type;

    /** @var bool */
    private $required;

    /** @var array */
    private $settings;

    /** @var string */
    private $category;

    /**
     * ListField constructor.
     *
     * @param string $category
     */
    public function __construct(
        string $name,
        string $handle,
        string $type,
        bool $required = false,
        array $settings = [],
        string $category = null
    ) {
        $this->name = $name;
        $this->handle = $handle;
        $this->type = $type;
        $this->required = $required;
        $this->settings = $settings;
        $this->category = $category;
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

    /**
     * @return null|string
     */
    public function getCategory()
    {
        return $this->category;
    }
}
