<?php

namespace Solspace\ExpressForms\integrations\dto;

use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

class Resource
{
    /** @var IntegrationTypeInterface */
    private $type;

    /** @var string */
    private $name;

    /** @var string */
    private $handle;

    /** @var array */
    private $settings;

    /**
     * MailingList constructor.
     */
    public function __construct(IntegrationTypeInterface $type, string $name, string $handle, array $settings = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->handle = $handle;
        $this->settings = $settings;
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
