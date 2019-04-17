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
     * @param string $identificator
     *
     * @return FieldInterface|Field|null
     */
    public function get(string $identificator)
    {
        return $this->fieldsByUid[$identificator] ?? $this->fieldsByHandle[$identificator] ?? null;
    }

    /**
     * @return array
     */
    public function getFieldsByUuids(): array
    {
        return $this->fieldsByUid;
    }

    /**
     * @param FieldInterface $field
     *
     * @return FieldCollection
     */
    public function addField(FieldInterface $field): FieldCollection
    {
        if (array_key_exists($field->getUid(), $this->fieldsByUid)) {
            return $this;
        }

        $this->fields[]                            = $field;
        $this->fieldsByHandle[$field->getHandle()] = $field;
        $this->fieldsByUid[$field->getUid()]       = $field;

        return $this;
    }

    /**
     * @return array
     */
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
        if ($offset === null) {
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
