<?php

namespace Solspace\ExpressForms\fields;

use craft\base\ElementInterface;

abstract class MultipleValueField extends BaseField implements MultipleValueInterface
{
    public function normalizeValue($value, ?ElementInterface $element = null): mixed
    {
        if ($value && \is_string($value) && preg_match('/^\[|\{.*\]|\}$/', $value)) {
            $value = \GuzzleHttp\json_decode($value, true);
        }

        return $value;
    }

    public function serializeValue($value, ?ElementInterface $element = null): string
    {
        if (!\is_array($value)) {
            if (null === $value) {
                $value = [];
            } else {
                $value = [$value];
            }
        }

        return json_encode($value);
    }
}
