<?php

namespace Solspace\ExpressForms\fields;

class Email extends BaseField
{
    public function getType(): string
    {
        return self::TYPE_EMAIL;
    }
}
