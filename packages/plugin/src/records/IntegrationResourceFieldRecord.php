<?php

namespace Solspace\ExpressForms\records;

use craft\db\ActiveRecord;

/**
 * Class MailingListFieldRecord.
 *
 * @property int    $id
 * @property int    $resourceId
 * @property string $handle
 * @property string $name
 * @property string $type
 * @property string $required
 * @property array  $settings
 * @property string $category
 * @property int    $sortOrder
 */
class IntegrationResourceFieldRecord extends ActiveRecord
{
    public const TABLE = '{{%expressforms_resource_fields}}';

    public static function tableName(): string
    {
        return self::TABLE;
    }

    public function rules(): array
    {
        return [
            [['resourceId'], 'required'],
            [['handle', 'name', 'type'], 'required'],
        ];
    }

    public function safeAttributes(): array
    {
        return [
            'id',
            'resourceId',
            'handle',
            'name',
            'type',
            'required',
            'settings',
            'category',
            'sortOrder',
        ];
    }
}
