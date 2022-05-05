<?php

namespace Solspace\ExpressForms\fields;

class Options extends MultipleValueField
{
    public function getType(): string
    {
        return self::TYPE_OPTIONS;
    }

    /**
     * A helper function for the CP submission editor.
     *
     * @param array $values
     */
    public function getValueDictionaryFromValues(mixed $values): array
    {
        if (!\is_array($values)) {
            $values = [$values];
        }

        $dictionary = [];
        foreach ($values as $value) {
            $dictionary[$value] = $value;
        }

        return $dictionary;
    }
}
