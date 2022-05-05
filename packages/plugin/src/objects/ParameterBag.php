<?php

namespace Solspace\ExpressForms\objects;

use Traversable;

class ParameterBag implements \IteratorAggregate, \ArrayAccess
{
    public function __construct(private array $bag = [])
    {
    }

    public function get(string $name, mixed $defaultValue = null): mixed
    {
        return $this->bag[$name] ?? $defaultValue;
    }

    public function add(string $name, mixed $value = null): self
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

    public function getIterator(): Traversable|\ArrayIterator
    {
        return new \ArrayIterator($this->bag);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->bag[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $this->bag[] = $value;
        } else {
            $this->bag[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->bag[$offset]);
    }
}
