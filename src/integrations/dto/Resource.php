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
     *
     * @param IntegrationTypeInterface $type
     * @param string                   $name
     * @param string                   $handle
     * @param array                    $settings
     */
    public function __construct(IntegrationTypeInterface $type, string $name, string $handle, array $settings = [])
    {
        $this->type     = $type;
        $this->name     = $name;
        $this->handle   = $handle;
        $this->settings = $settings;
    }

    /**
     * @return IntegrationTypeInterface
     */
    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
