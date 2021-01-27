<?php

namespace Solspace\ExpressForms\objects\Export;

class ArrayField extends ExportField
{
    /**
     * @param mixed $value
     *
     * @return null|array
     */
    public function transformValue($value)
    {
        if ($value && \is_string($value) && preg_match('/^\[|\{.*\]|\}$/', $value)) {
            $value = \GuzzleHttp\json_decode($value, true);
        }

        if (null !== $value && !\is_array($value)) {
            $value = [$value];
        }

        return $value ?? [];
    }
}
