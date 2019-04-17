<?php

namespace Solspace\ExpressForms\events\integrations;

use yii\base\Event;

class IntegrationValueMappingEvent extends Event
{
    /** @var array */
    private $mappedValues;

    /**
     * IntegrationValueMappingEvent constructor.
     *
     * @param array $mappedValues
     */
    public function __construct(array $mappedValues = [])
    {
        $this->mappedValues = $mappedValues;

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getMappedValues(): array
    {
        return $this->mappedValues ?? [];
    }

    /**
     * @param array $mappedValues
     *
     * @return IntegrationValueMappingEvent
     */
    public function setMappedValues(array $mappedValues = []): IntegrationValueMappingEvent
    {
        $this->mappedValues = $mappedValues;

        return $this;
    }
}
