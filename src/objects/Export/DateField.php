<?php

namespace Solspace\ExpressForms\objects\Export;

class DateField extends ExportField
{
    /**
     * @param mixed $value
     *
     * @return \DateTime|mixed|null
     */
    public function transformValue($value)
    {
        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
