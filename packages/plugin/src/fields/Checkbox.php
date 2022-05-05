<?php

namespace Solspace\ExpressForms\fields;

use craft\base\ElementInterface;
use yii\db\Schema;

class Checkbox extends BaseField
{
    public function getType(): string
    {
        return self::TYPE_CHECKBOX;
    }

    public function normalizeValue($value, ?ElementInterface $element = null): bool
    {
        return (bool) $value;
    }

    public function serializeValue($value, ?ElementInterface $element = null): bool
    {
        return $value ? '1' : '0';
    }

    public function getContentColumnType(): string
    {
        return Schema::TYPE_BOOLEAN;
    }
}
