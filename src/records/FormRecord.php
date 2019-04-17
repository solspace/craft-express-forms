<?php

namespace Solspace\ExpressForms\records;

use Craft;
use craft\db\ActiveRecord;
use craft\db\Query;
use Solspace\ExpressForms\elements\Submission;

/**
 * Class FormRecord
 *
 * @property int    $id
 * @property string $uuid
 * @property int    $fieldLayoutId
 * @property string $name
 * @property string $handle
 * @property string $description
 * @property string $color
 * @property string $submissionTitle
 * @property bool   $saveSubmissions
 * @property string $adminNotification
 * @property string $adminEmails
 * @property string $submitterNotification
 * @property string $submitterEmailField
 * @property int    $spamCount
 * @property array  $fields
 * @property array  $integrations
 * @property int    $sortOrder
 */
class FormRecord extends ActiveRecord
{
    const TABLE     = '{{%expressforms_forms}}';
    const TABLE_STD = 'expressforms_forms';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return self::TABLE;
    }

    /**
     * @param string $handle
     *
     * @return string
     */
    public static function getUniqueHandle(string $handle): string
    {
        $i = 1;

        if (preg_match('/^(.*)-(\d+)$/', $handle, $matches)) {
            $handle = $matches[1];
            $i      = ((int) $matches[2]) + 1;
        }

        do {
            $newHandle = $handle . "-$i";
            $i++;

            $exists = (new Query())
                ->select('id')
                ->from(self::TABLE)
                ->where(['handle' => $newHandle])
                ->scalar();

            if (!$exists) {
                break;
            }
        } while ($i < 100);

        return $newHandle;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['handle'], 'unique'],
            [['name', 'handle'], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function safeAttributes(): array
    {
        return [
            'id',
            'uuid',
            'fieldLayoutId',
            'name',
            'handle',
            'description',
            'color',
            'submissionTitle',
            'saveSubmissions',
            'adminNotification',
            'adminEmails',
            'submitterNotification',
            'submitterEmailField',
            'spamCount',
            'fields',
            'integrations',
            'sortOrder',
        ];
    }

    public function afterDelete()
    {
        $fields = \Craft::$app->getFields();

        $oldContentTable      = Craft::$app->getContent()->contentTable;
        $oldFieldColumnPrefix = Craft::$app->getContent()->fieldColumnPrefix;

        Craft::$app->getContent()->contentTable = Submission::getContentTableNameFromHandle($this->handle);
        Craft::$app->getContent()->fieldContext = Submission::getFieldContextNameFromId($this->id);

        $layout = $fields->getLayoutById($this->fieldLayoutId ?? 0);
        if ($layout) {
            foreach ($layout->getFields() as $field) {
                $fields->deleteFieldById($field->id);
            }

            $fields->deleteLayoutById($this->fieldLayoutId);
        }

        \Craft::$app->db->createCommand()
            ->dropTableIfExists(Submission::getContentTableNameFromHandle($this->handle))
            ->execute();

        Craft::$app->getContent()->contentTable      = $oldContentTable;
        Craft::$app->getContent()->fieldColumnPrefix = $oldFieldColumnPrefix;
    }
}
