<?php

namespace Solspace\ExpressForms\fields;

class Hidden extends BaseField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_HIDDEN;
    }
}
