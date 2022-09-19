<?php

namespace Solspace\ExpressForms\records;

use craft\db\ActiveRecord;

/**
 * Class MailingListRecord.
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
    public const TABLE = '{{%expressforms_resources}}';

    public static function tableName(): string
    {
        return self::TABLE;
    }

    public function rules(): array
    {
        return [
            [['typeClass'], 'required'],
            [['handle', 'name'], 'required'],
            [['handle'], 'unique', 'targetAttribute' => ['typeClass', 'handle']],
        ];
    }

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
