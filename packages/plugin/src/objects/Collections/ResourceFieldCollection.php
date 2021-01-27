<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\integrations\dto\ResourceField;
use Traversable;

class ResourceFieldCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var ResourceField[] */
    private $fields = [];

    /**
     * @return null|ResourceField
     */
    public function get(string $identificator)
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

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->fieldsByHandle[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->fields[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }
}
