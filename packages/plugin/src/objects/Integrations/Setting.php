<?php

namespace Solspace\ExpressForms\objects\Integrations;

class Setting
{
    const TYPE_TEXT = 'text';
    const TYPE_BOOLEAN = 'boolean';

    /** @var string */
    private $label;

    /** @var string */
    private $handle;

    /** @var string */
    private $description;

    /** @var string */
    private $type;

    /** @var bool */
    private $required;

    /**
     * Setting constructor.
     */
    public function __construct(
        string $label,
        string $handle,
        string $description = null,
        string $type = self::TYPE_TEXT,
        bool $required = false
    ) {
        $this->label = $label;
        $this->handle = $handle;
        $this->description = $description;
        $this->type = $type;
        $this->required = $required;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return null|string
     */
    public function getDescription()
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
