<?php

namespace Solspace\ExpressForms\fields;

use yii\db\Schema;

class Textarea extends BaseField
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_TEXTAREA;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }
}
