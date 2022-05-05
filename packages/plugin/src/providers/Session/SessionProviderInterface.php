<?php

namespace Solspace\ExpressForms\providers\Session;

interface SessionProviderInterface
{
    public function get(string $key, mixed $defaultValue = null): mixed;

    public function set(string $key, mixed $value): self;

    public function remove(string $key): mixed;
}
