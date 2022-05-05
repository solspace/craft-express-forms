<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\integrations\dto\ResourceField;
use Traversable;

class ResourceFieldCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var ResourceField[] */
    private array $fields = [];

    public function get(string $identificator): ?ResourceField
    {
        return $this->fields[$identificator] ?? null;
    }

    public function addField(ResourceField $field): self
    {
        if (\array_key_exists($field->getHandle(), $this->fields)) {
            return $this;
        }

        $this->fields[$field->getHandle()] = $field;

        return $this;
    }

    public function asArray(): array
    {
        return $this->fields;
    }

    public function getIterator(): Traversable|\ArrayIterator
    {
        return new \ArrayIterator($this->fields);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet(mixed $offset): ?ResourceField
    {
        return $this->fields[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->fields[$offset]);
    }
}
