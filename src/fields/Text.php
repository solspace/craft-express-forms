<?php

namespace Solspace\ExpressForms\fields;

class Text extends BaseField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_TEXT;
    }
}
