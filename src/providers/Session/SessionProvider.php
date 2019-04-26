<?php

namespace Solspace\ExpressForms\providers\Session;

class SessionProvider implements SessionProviderInterface
{
    /**
     * @param string $key
     * @param null   $defaultValue
     *
     * @return mixed
     */
    public function get(string $key, $defaultValue = null)
    {
        return \Craft::$app->getSession()->get($key, $defaultValue);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return SessionProviderInterface
     */
    public function set(string $key, $value): SessionProviderInterface
    {
        \Craft::$app->getSession()->set($key, $value);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function remove(string $key)
    {
        return \Craft::$app->getSession()->remove($key);
    }
}
