<?php

namespace Solspace\ExpressForms\fields;

class Options extends MultipleValueField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_OPTIONS;
    }

    /**
     * A helper function for the CP submission editor
     *
     * @param array $values
     *
     * @return array
     */
    public function getValueDictionaryFromValues($values): array
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        $dictionary = [];
        foreach ($values as $value) {
            $dictionary[$value] = $value;
        }

        return $dictionary;
    }
}
