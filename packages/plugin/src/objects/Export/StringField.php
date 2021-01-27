<?php

namespace Solspace\ExpressForms\objects\Export;

class StringField extends ExportField
{
    /**
     * @param mixed $value
     *
     * @return mixed|string
     */
    public function transformValue($value)
    {
        return (string) $value;
    }
}
