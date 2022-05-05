<?php

namespace Solspace\ExpressForms\objects\Export;

class ArrayField extends ExportField
{
    public function transformValue(mixed $value): ?array
    {
        if ($value && \is_string($value) && preg_match('/^\[|\{.*\]|\}$/', $value)) {
            $value = json_decode($value, true);
        }

        if (null !== $value && !\is_array($value)) {
            $value = [$value];
        }

        return $value ?? [];
    }
}
