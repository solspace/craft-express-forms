<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\integrations\CrmTypeInterface;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Traversable;

class IntegrationMappingCollection implements \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    /** @var IntegrationMappingInterface[] */
    private array $integrationMappings = [];

    /** @var IntegrationMappingInterface[] */
    private array $mailingListMappings = [];

    /** @var IntegrationMappingInterface[] */
    private array $crmMappings = [];

    /**
     * @return null|IntegrationMappingInterface
     */
    public function get(string $identificator)
    {
        return $this->integrationMappings[$identificator] ?? null;
    }

    public function addMapping(IntegrationMappingInterface $integrationMapping): self
    {
        $handle = $integrationMapping->getHandle();
        if (\array_key_exists($handle, $this->integrationMappings)) {
            return $this;
        }

        $this->integrationMappings[$integrationMapping->getHandle()] = $integrationMapping;

        if ($integrationMapping->getType() instanceof MailingListTypeInterface) {
            $this->mailingListMappings[$handle] = $integrationMapping;
        } elseif ($integrationMapping->getType() instanceof CrmTypeInterface) {
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

    public function asArray(): array
    {
        return $this->integrationMappings;
    }

    public function jsonSerialize(): array
    {
        return $this->asArray();
    }

    public function getIterator(): Traversable|\ArrayIterator
    {
        return new \ArrayIterator($this->integrationMappings);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet($offset): ?IntegrationMappingInterface
    {
        return $this->integrationMappings[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->integrationMappings[] = $value;
        } else {
            $this->integrationMappings[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->integrationMappings[$offset]);
    }
}
