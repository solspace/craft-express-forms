<?php

namespace Solspace\ExpressForms\decorators;

use yii\base\Event;

abstract class AbstractDecorator implements ExpressFormDecoratorInterface
{
    public function initEventListeners()
    {
        foreach ($this->getEventListenerList() as list($class, $event, $callback)) {
            Event::on($class, $event, $callback);
        }
    }

    public function destructEventListeners()
    {
        foreach ($this->getEventListenerList() as list($class, $event, $callback)) {
            Event::off($class, $event, $callback);
        }
    }
}
