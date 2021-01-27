<?php

namespace Solspace\ExpressForms\providers\Session;

class SessionProvider implements SessionProviderInterface
{
    /**
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, $defaultValue = null)
    {
        return \Craft::$app->getSession()->get($key, $defaultValue);
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): SessionProviderInterface
    {
        \Craft::$app->getSession()->set($key, $value);

        return $this;
    }

    /**
     * @return mixed
     */
    public function remove(string $key)
    {
        return \Craft::$app->getSession()->remove($key);
    }
}
