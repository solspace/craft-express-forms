<?php

namespace Solspace\ExpressForms\records;

use craft\db\ActiveRecord;

/**
 * Class MailingListRecord
 *
 * @package Solspace\ExpressForms\records
 *
 * @property int    $id
 * @property string $typeClass
 * @property string $handle
 * @property string $name
 * @property array  $settings
 * @property int    $sortOrder
 */
class IntegrationResourceRecord extends ActiveRecord
{
    const TABLE = '{{%expressforms_resources}}';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return self::TABLE;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['typeClass'], 'required'],
            [['handle', 'name'], 'required'],
            [['handle'], 'unique'],
        ];
    }

    /**
     * @return array
     */
    public function safeAttributes(): array
    {
        return [
            'id',
            'typeClass',
            'handle',
            'name',
            'settings',
            'sortOrder',
        ];
    }
}
