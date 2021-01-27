<?php

namespace Solspace\ExpressForms\events\integrations;

use yii\base\Event;

class IntegrationValueMappingEvent extends Event
{
    /** @var array */
    private $mappedValues;

    /**
     * IntegrationValueMappingEvent constructor.
     */
    public function __construct(array $mappedValues = [])
    {
        $this->mappedValues = $mappedValues;

        parent::__construct();
    }

    public function getMappedValues(): array
    {
        return $this->mappedValues ?? [];
    }

    public function setMappedValues(array $mappedValues = []): self
    {
        $this->mappedValues = $mappedValues;

        return $this;
    }
}
