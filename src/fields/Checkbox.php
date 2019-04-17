<?php

namespace Solspace\ExpressForms\fields;

use craft\base\ElementInterface;
use yii\db\Schema;

class Checkbox extends BaseField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_CHECKBOX;
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return mixed
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return (bool) $value;
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|string|null
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return (bool) $value ? '1' : '0';
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_BOOLEAN;
    }
}
