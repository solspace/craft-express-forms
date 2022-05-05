<?php

namespace Solspace\ExpressForms\decorators;

use yii\base\Event;

abstract class AbstractDecorator implements ExpressFormDecoratorInterface
{
    public function initEventListeners(): void
    {
        foreach ($this->getEventListenerList() as [$class, $event, $callback]) {
            Event::on($class, $event, $callback);
        }
    }

    public function destructEventListeners(): void
    {
        foreach ($this->getEventListenerList() as [$class, $event, $callback]) {
            Event::off($class, $event, $callback);
        }
    }
}
