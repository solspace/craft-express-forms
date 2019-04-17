<?php

namespace Solspace\ExpressForms\objects\Integrations;

class Setting
{
    const TYPE_TEXT    = 'text';
    const TYPE_BOOLEAN = 'boolean';

    /** @var string */
    private $label;

    /** @var string */
    private $handle;

    /** @var string */
    private $type;

    /** @var bool */
    private $required;

    /**
     * Setting constructor.
     *
     * @param string $label
     * @param string $handle
     * @param string $type
     * @param bool   $required
     */
    public function __construct(string $label, string $handle, string $type = self::TYPE_TEXT, bool $required = false)
    {
        $this->label    = $label;
        $this->handle   = $handle;
        $this->type     = $type;
        $this->required = $required;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }
}
