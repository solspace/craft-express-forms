<?php

namespace Solspace\ExpressForms\objects\Export;

class IntField extends ExportField
{
    /**
     * @param mixed $value
     *
     * @return int|mixed
     */
    public function transformValue($value)
    {
        return (int) $value;
    }
}
