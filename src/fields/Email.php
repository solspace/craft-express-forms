<?php

namespace Solspace\ExpressForms\fields;

class Email extends BaseField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_EMAIL;
    }
}
