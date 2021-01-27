<?php

namespace Solspace\ExpressForms\objects\Collections;

use craft\base\Field;
use Solspace\ExpressForms\fields\FieldInterface;
use Traversable;

class FieldCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var FieldInterface[] */
    private $fields = [];

    /** @var FieldInterface[] */
    private $fieldsByHandle = [];

    /** @var FieldInterface[] */
    private $fieldsByUid = [];

    /**
     * @return null|Field|FieldInterface
     */
    public function get(string $identificator)
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

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fieldsByHandle);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->fieldsByHandle[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->fieldsByHandle[] = $value;
        } else {
            $this->fieldsByHandle[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->fieldsByHandle[$offset]);
    }
}
