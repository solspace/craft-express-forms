<?php

namespace Solspace\ExpressForms\objects\Collections;

use Solspace\ExpressForms\integrations\dto\ResourceField;
use Traversable;

class ResourceFieldCollection implements \IteratorAggregate, \ArrayAccess
{
    /** @var ResourceField[] */
    private $fields = [];

    /**
     * @param string $identificator
     *
     * @return ResourceField|null
     */
    public function get(string $identificator)
    {
        return $this->fields[$identificator] ?? null;
    }

    /**
     * @param ResourceField $field
     *
     * @return ResourceFieldCollection
     */
    public function addField(ResourceField $field): ResourceFieldCollection
    {
        if (array_key_exists($field->getHandle(), $this->fields)) {
            return $this;
        }

        $this->fields[$field->getHandle()] = $field;

        return $this;
    }

    /**
     * @return array
     */
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
        if ($offset === null) {
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
