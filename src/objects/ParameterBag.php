<?php

namespace Solspace\ExpressForms\objects;

use Traversable;

class ParameterBag implements \IteratorAggregate, \ArrayAccess
{
    /** @var array */
    private $bag = [];

    /**
     * ParameterBag constructor.
     *
     * @param array $bag
     */
    public function __construct(array $bag = [])
    {
        $this->bag = $bag;
    }

    /**
     * @param string $name
     * @param null   $defaultValue
     *
     * @return mixed|null
     */
    public function get(string $name, $defaultValue = null)
    {
        return $this->bag[$name] ?? $defaultValue;
    }

    /**
     * @param string $name
     * @param null   $value
     *
     * @return ParameterBag
     */
    public function add(string $name, $value = null): ParameterBag
    {
        $this->bag[$name] = $value;

        return $this;
    }

    /**
     * @param array $bag
     *
     * @return ParameterBag
     */
    public function set(array $bag): ParameterBag
    {
        $this->bag = $bag;

        return $this;
    }

    /**
     * @param array $bag
     *
     * @return ParameterBag
     */
    public function merge(array $bag): ParameterBag
    {
        $this->bag = array_merge($this->bag, $bag);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ParameterBag
     */
    public function remove(string $name): ParameterBag
    {
        if (array_key_exists($name, $this->bag)) {
            unset($this->bag[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->bag[$name]);
    }

    /**
     * @return array
     */
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
        if ($offset === null) {
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
