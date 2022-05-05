<?php

namespace Solspace\ExpressForms\providers\Session;

interface FlashBagProviderInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): self;
}
