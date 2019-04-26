<?php

namespace Solspace\ExpressForms\providers\Session;

interface SessionProviderInterface
{
    /**
     * @param string $key
     * @param null   $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, $defaultValue = null);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return SessionProviderInterface
     */
    public function set(string $key, $value): SessionProviderInterface;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function remove(string $key);
}
