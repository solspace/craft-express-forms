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

    public function addType(string $type): self
    {
        $handle = StringHelper::toKebabCase($type);

        $this->types[$handle] = $type;

        return $this;
    }

    /**
     * @param mixed $types
     */
    public function setTypes($types): self
    {
        $this->types = $types;

        return $this;
    }
}
