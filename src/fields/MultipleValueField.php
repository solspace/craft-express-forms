<?php

namespace Solspace\ExpressForms\fields;

use craft\base\ElementInterface;

abstract class MultipleValueField extends BaseField implements MultipleValueInterface
{
    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return mixed
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value && is_string($value) && preg_match('/^\[|\{.*\]|\}$/', $value)) {
            $value = \GuzzleHttp\json_decode($value, true);
        }

        return $value;
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|string|null
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if (!is_array($value)) {
            if (null === $value) {
                $value = [];
            } else {
                $value = [$value];
            }
        }

        return \GuzzleHttp\json_encode($value);
    }
}
