<?php

namespace Solspace\ExpressForms\providers\Session;

class FlashBagProvider implements FlashBagProviderInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return \Craft::$app->getSession()->getFlash($key, $default);
    }

    public function set(string $key, mixed $value): FlashBagProviderInterface
    {
        \Craft::$app->getSession()->setFlash($key, $value);

        return $this;
    }
}
