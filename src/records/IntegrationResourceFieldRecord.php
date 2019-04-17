<?php

namespace Solspace\ExpressForms\records;

use craft\db\ActiveRecord;

/**
 * Class MailingListFieldRecord
 *
 * @package Solspace\ExpressForms\records
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
    const TABLE = '{{%expressforms_resource_fields}}';

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
            [['resourceId'], 'required'],
            [['handle', 'name', 'type'], 'required'],
        ];
    }

    /**
     * @return array
     */
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
