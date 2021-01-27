<?php

namespace Solspace\ExpressForms\objects\Export;

class BooleanField extends ExportField
{
    /**
     * @param mixed $value
     *
     * @return bool|mixed
     */
    public function transformValue($value)
    {
        return (bool) $value;
    }
}
