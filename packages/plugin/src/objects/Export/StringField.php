<?php

namespace Solspace\ExpressForms\objects\Export;

class StringField extends ExportField
{
    public function transformValue(mixed $value): string
    {
        return (string) $value;
    }
}
