<?php

namespace Solspace\ExpressForms\objects\Export;

class DateField extends ExportField
{
    public function transformValue(mixed $value): ?\DateTime
    {
        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
