<?php

namespace Solspace\ExpressForms\fields;

class Text extends BaseField
{
    public function getType(): string
    {
        return self::TYPE_TEXT;
    }
}
