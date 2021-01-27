<?php

namespace Solspace\ExpressForms\providers\Session;

interface FlashBagProviderInterface
{
    /**
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): self;
}
