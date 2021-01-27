<?php

namespace Solspace\ExpressForms\fields;

class Hidden extends BaseField
{
    public function getType(): string
    {
        return self::TYPE_HIDDEN;
    }
}
