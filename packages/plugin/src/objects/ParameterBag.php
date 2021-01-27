<?php

namespace Solspace\ExpressForms\objects;

use Traversable;

class ParameterBag implements \IteratorAggregate, \ArrayAccess
{
    /** @var array */
    private $bag = [];

    /**
     * ParameterBag constructor.
     */
    public function __construct(array $bag = [])
    {
        $this->bag = $bag;
    }

    /**
     * @param null $defaultValue
     *
     * @return null|mixed
     */
    public function get(string $name, $defaultValue = null)
    {
        return $this->bag[$name] ?? $defaultValue;
    }

    /**
     * @param null $value
     */
    public function add(string $name, $value = null): self
    {
        $this->bag[$name] = $value;

        return $this;
    }

    public function set(array $bag): self
    {
        $this->bag = $bag;

        return $this;
    }

    public function merge(array $bag): self
    {
        $this->bag = array_merge($this->bag, $bag);

        return $this;
    }

    public function remove(string $name): self
    {
        if (\array_key_exists($name, $this->bag)) {
            unset($this->bag[$name]);
        }

        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->bag[$name]);
    }

    public function toArray(): array
    {
        return $this->bag;
    }

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->bag);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->bag[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->bag[] = $value;
        } else {
            $this->bag[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->bag[$offset]);
    }
}
