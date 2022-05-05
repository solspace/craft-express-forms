<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\fields\FieldInterface;
use Traversable;

class FieldCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var FieldInterface[] */
    private array $fields = [];

    /** @var FieldInterface[] */
    private array $fieldsByHandle = [];

    /** @var FieldInterface[] */
    private array $fieldsByUid = [];

    public function get(string $identificator): ?FieldInterface
    {
        return $this->fieldsByUid[$identificator] ?? $this->fieldsByHandle[$identificator] ?? null;
    }

    public function getFieldsByUuids(): array
    {
        return $this->fieldsByUid;
    }

    public function addField(FieldInterface $field): self
    {
        if (\array_key_exists($field->getUid(), $this->fieldsByUid)) {
            return $this;
        }

        $this->fields[] = $field;
        $this->fieldsByHandle[$field->getHandle()] = $field;
        $this->fieldsByUid[$field->getUid()] = $field;

        return $this;
    }

    public function asArray(): array
    {
        return $this->fieldsByHandle;
    }

    public function getIterator(): Traversable|\ArrayIterator
    {
        return new \ArrayIterator($this->fieldsByHandle);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->fieldsByHandle[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->fieldsByHandle[] = $value;
        } else {
            $this->fieldsByHandle[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->fieldsByHandle[$offset]);
    }
}
