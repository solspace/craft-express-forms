<?php

namespace Solspace\ExpressForms\events\export;

use craft\helpers\StringHelper;
use yii\base\Event;

class RegisterExportTypesEvent extends Event
{
    private $types = [];

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $type
     *
     * @return RegisterExportTypesEvent
     */
    public function addType(string $type): RegisterExportTypesEvent
    {
        $handle = StringHelper::toKebabCase($type);

        $this->types[$handle] = $type;

        return $this;
    }

    /**
     * @param mixed $types
     *
     * @return RegisterExportTypesEvent
     */
    public function setTypes($types): RegisterExportTypesEvent
    {
        $this->types = $types;

        return $this;
    }
}
