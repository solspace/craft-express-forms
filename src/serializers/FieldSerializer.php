<?php

namespace Solspace\ExpressForms\serializers;

use Solspace\ExpressForms\fields\FieldInterface;

class FieldSerializer
{
    /**
     * @param FieldInterface $field
     *
     * @return string
     */
    public function toJson(FieldInterface $field): string
    {
        return \GuzzleHttp\json_encode($field);
    }

    /**
     * @param FieldInterface $field
     *
     * @return array|null
     */
    public function toArray(FieldInterface $field)
    {
        if (null === $field) {
            return null;
        }

        return $field->jsonSerialize();
    }
}
