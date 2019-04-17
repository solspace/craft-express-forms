<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\integrations\CrmTypeInterface;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Traversable;

class IntegrationMappingCollection implements \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    /** @var IntegrationMappingInterface[] */
    private $integrationMappings = [];

    /** @var IntegrationMappingInterface[] */
    private $mailingListMappings = [];

    /** @var IntegrationMappingInterface[] */
    private $crmMappings = [];

    /**
     * @param string $identificator
     *
     * @return IntegrationMappingInterface|null
     */
    public function get(string $identificator)
    {
        return $this->integrationMappings[$identificator] ?? null;
    }

    /**
     * @param IntegrationMappingInterface $integrationMapping
     *
     * @return IntegrationMappingCollection
     */
    public function addMapping(IntegrationMappingInterface $integrationMapping): IntegrationMappingCollection
    {
        $handle = $integrationMapping->getHandle();
        if (array_key_exists($handle, $this->integrationMappings)) {
            return $this;
        }

        $this->integrationMappings[$integrationMapping->getHandle()] = $integrationMapping;

        if ($integrationMapping->getType() instanceof MailingListTypeInterface) {
            $this->mailingListMappings[$handle] = $integrationMapping;
        } else if ($integrationMapping->getType() instanceof CrmTypeInterface) {
            $this->crmMappings[$handle] = $integrationMapping;
        }

        return $this;
    }

    /**
     * @return IntegrationMappingInterface[]
     */
    public function getMailingListMappings(): array
    {
        return $this->mailingListMappings;
    }

    /**
     * @return IntegrationMappingInterface[]
     */
    public function getCrmMappings(): array
    {
        return $this->crmMappings;
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return $this->integrationMappings;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->integrationMappings);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->integrationMappings[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->integrationMappings[] = $value;
        } else {
            $this->integrationMappings[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->integrationMappings[$offset]);
    }
}
