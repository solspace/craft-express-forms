<?php

namespace Solspace\ExpressForms\objects\Export;

class IntField extends ExportField
{
    public function transformValue(mixed $value): int
    {
        return (int) $value;
    }
}
