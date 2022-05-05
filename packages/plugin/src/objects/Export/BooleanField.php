<?php

namespace Solspace\ExpressForms\objects\Export;

class BooleanField extends ExportField
{
    public function transformValue(mixed $value): bool
    {
        return (bool) $value;
    }
}
