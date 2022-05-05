<?php

namespace Solspace\ExpressForms\serializers;

use Solspace\ExpressForms\fields\FieldInterface;

class FieldSerializer
{
    public function toJson(FieldInterface $field): string
    {
        return json_encode($field);
    }

    public function toArray(FieldInterface $field): array
    {
        return $field->jsonSerialize();
    }
}
