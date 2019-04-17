<?php

namespace Solspace\ExpressForms\providers\Session;

interface FlashBagProviderInterface
{
    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return FlashBagProviderInterface
     */
    public function set(string $key, $value): FlashBagProviderInterface;
}
