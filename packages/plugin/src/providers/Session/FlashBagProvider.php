<?php

namespace Solspace\ExpressForms\providers\Session;

class FlashBagProvider implements FlashBagProviderInterface
{
    /**
     * @param null $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return \Craft::$app->getSession()->getFlash($key, $default);
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): FlashBagProviderInterface
    {
        \Craft::$app->getSession()->setFlash($key, $value);

        return $this;
    }
}
