<?php

namespace Solspace\ExpressForms\serializers;

use Solspace\ExpressForms\fields\FieldInterface;

class FieldSerializer
{
    public function toJson(FieldInterface $field): string
    {
        return \GuzzleHttp\json_encode($field);
    }

    /**
     * @return null|array
     */
    public function toArray(FieldInterface $field)
    {
        if (null === $field) {
            return null;
        }

        return $field->jsonSerialize();
    }
}
