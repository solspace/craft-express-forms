<?php

namespace Solspace\ExpressForms\providers\Session;

interface SessionProviderInterface
{
    /**
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, $defaultValue = null);

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): self;

    /**
     * @return mixed
     */
    public function remove(string $key);
}
