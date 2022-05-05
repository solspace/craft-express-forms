<?php

namespace Solspace\ExpressForms\providers\Session;

class SessionProvider implements SessionProviderInterface
{
    public function get(string $key, mixed $defaultValue = null): mixed
    {
        return \Craft::$app->getSession()->get($key, $defaultValue);
    }

    public function set(string $key, mixed $value): SessionProviderInterface
    {
        \Craft::$app->getSession()->set($key, $value);

        return $this;
    }

    public function remove(string $key): mixed
    {
        return \Craft::$app->getSession()->remove($key);
    }
}
