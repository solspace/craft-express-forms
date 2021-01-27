<?php

namespace Solspace\ExpressForms\services;

use craft\db\Query;
use craft\db\Table;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Responses\FormSaveResponse;
use Solspace\ExpressForms\records\FormRecord;
use yii\db\Connection;
use yii\db\SchemaBuilderTrait;

class Forms extends BaseService
{
    use SchemaBuilderTrait;

    private static $allFormsLoaded = false;
    private static $formIdCache = [];
    private static $formUuidCache = [];
    private static $formHandleCache = [];

    /**
     * @return null|Form
     */
    public function getFormById(int $id)
    {
        if (!isset(self::$formIdCache[$id])) {
            $result = $this->getQuery()
                ->where([FormRecord::TABLE.'.[[id]]' => $id])
                ->one()
            ;

            $form = null;
            if ($result) {
                $form = $this->createFormFromDbData($result);

                self::$formHandleCache[$form->getHandle()] = $form;
                self::$formUuidCache[$form->getUuid()] = $form;
            }

            self::$formIdCache[$id] = $form;
        }

        return self::$formIdCache[$id];
    }

    /**
     * @return null|Form
     */
    public function getFormByHandle(string $handle)
    {
        if (!isset(self::$formHandleCache[$handle])) {
            $result = $this->getQuery()
                ->where([FormRecord::TABLE.'.[[handle]]' => $handle])
                ->one()
            ;

            $form = null;
            if ($result) {
                $form = $this->createFormFromDbData($result);

                self::$formIdCache[$form->getId()] = $form;
                self::$formUuidCache[$form->getUuid()] = $form;
            }

            self::$formHandleCache[$handle] = $form;
        }

        return self::$formHandleCache[$handle];
    }

    /**
     * @return null|Form
     */
    public function getFormByUuid(string $uuid)
    {
        if (!isset(self::$formUuidCache[$uuid])) {
            $result = $this->getQuery()
                ->where([FormRecord::TABLE.'.[[uuid]]' => $uuid])
                ->one()
            ;

            $form = null;
            if ($result) {
                $form = $this->createFormFromDbData($result);

                self::$formHandleCache[$form->getHandle()] = $form;
                self::$formIdCache[$form->getId()] = $form;
            }

            self::$formUuidCache[$uuid] = $form;
        }

        return self::$formUuidCache[$uuid];
    }

    /**
     * @param int|string $idOrHandle
     *
     * @return null|Form
     */
    public function getFormByIdOrHandle($idOrHandle)
    {
        if (is_numeric($idOrHandle)) {
            return $this->getFormById($idOrHandle);
        }

        return $this->getFormByHandle($idOrHandle);
    }

    /**
     * @return Form[]
     */
    public function getAllForms(bool $indexById = false): array
    {
        if (!self::$allFormsLoaded) {
            $resultItems = $this->getQuery()->all();

            foreach ($resultItems as $result) {
                $form = $this->createFormFromDbData($result);
                if (null !== $form) {
                    self::$formIdCache[$form->getId()] = $form;
                    self::$formHandleCache[$form->getHandle()] = $form;
                }
            }

            self::$allFormsLoaded = true;
        }

        return $indexById ? self::$formIdCache : array_values(self::$formIdCache);
    }

    public function save(Form $form): FormSaveResponse
    {
        $isNew = !$form->getId();

        if (!$isNew) {
            $record = FormRecord::findOne(['uuid' => $form->getUuid()]);
        } else {
            $record = new FormRecord();
        }

        $oldFormHandle = $record->getOldAttribute('handle') ?? null;

        $attributes = ExpressForms::container()->formSerializer()->toArray($form);
        if ($isNew) {
            unset($attributes['id']);
        }

        $record->setAttributes($attributes);
        $record->save();

        if ($record->id && !$form->getId()) {
            $form->setId($record->id);
        }

        $response = new FormSaveResponse($form);
        if ($record->getErrors()) {
            $response->setErrors($record->getErrors());
        } else {
            $this->ensureContentTable($form, $oldFormHandle);
            ExpressForms::getInstance()->fields->updateFieldLayout($form);
        }

        return $response;
    }

    public function incrementSpamCount(Form $form)
    {
        $this->getDb()
            ->createCommand()
            ->update(
                FormRecord::TABLE,
                ['spamCount' => $form->getSpamCount() + 1],
                ['id' => $form->getId()]
            )
            ->execute()
        ;
    }

    public function deleteById(int $id): bool
    {
        $record = FormRecord::findOne(['id' => $id]);
        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }

    /**
     * @return \craft\db\Connection
     */
    protected function getDb(): Connection
    {
        return \Craft::$app->getDb();
    }

    private function createFormFromDbData(array $data): Form
    {
        $data['integrations'] = \GuzzleHttp\json_decode($data['integrations'] ?? '[]', true);

        $form = ExpressForms::container()->formFactory()->populateFromArray(new Form(), $data);
        $this->attachSubmissionCount($form);

        return $form;
    }

    private function getQuery(): Query
    {
        $formTable = FormRecord::TABLE;

        return (new Query())
            ->select(
                [
                    $formTable.'.[[id]]',
                    $formTable.'.[[uuid]]',
                    $formTable.'.[[fieldLayoutId]]',
                    $formTable.'.[[name]]',
                    $formTable.'.[[handle]]',
                    $formTable.'.[[description]]',
                    $formTable.'.[[color]]',
                    $formTable.'.[[submissionTitle]]',
                    $formTable.'.[[saveSubmissions]]',
                    $formTable.'.[[adminNotification]]',
                    $formTable.'.[[adminEmails]]',
                    $formTable.'.[[submitterNotification]]',
                    $formTable.'.[[submitterEmailField]]',
                    $formTable.'.[[spamCount]]',
                    $formTable.'.[[integrations]]',
                ]
            )
            ->from($formTable)
            ->groupBy($formTable.'.[[id]]')
            ->orderBy([$formTable.'.[[sortOrder]]' => \SORT_ASC])
        ;
    }

    private function attachSubmissionCount(Form $form)
    {
        $elements = Table::ELEMENTS;
        $submissions = Submission::TABLE;

        static $countTable;
        if (null === $countTable) {
            $results = (new Query())
                ->select(["{$submissions}.[[formId]]", "COUNT({$submissions}.[[id]]) AS count"])
                ->from($submissions)
                ->innerJoin($elements, "{$elements}.[[id]] = {$submissions}.[[id]]")
                ->where(["{$elements}.[[dateDeleted]]" => null])
                ->groupBy(["{$submissions}.[[formId]]"])
                ->all()
            ;

            $countTable = [];
            foreach ($results as $result) {
                $countTable[$result['formId']] = (int) $result['count'];
            }
        }

        $form->setSubmissionCount($countTable[$form->getId()] ?? 0);
    }

    /**
     * Creates or renames a content table for the given form.
     */
    private function ensureContentTable(Form $form, string $oldHandle = null)
    {
        $db = \Craft::$app->db;

        $tableName = Submission::getContentTableName($form);
        $tableNameStd = preg_replace('/^{{%(.*)}}$/', '$1', $tableName);

        if (null !== $oldHandle && $oldHandle !== $form->getHandle()) {
            $db->createCommand()
                ->renameTable(
                    Submission::getContentTableNameFromHandle($oldHandle),
                    $tableName
                )
                ->execute()
            ;
        }

        if (null === $oldHandle) {
            $db->createCommand()
                ->createTable(
                    $tableName,
                    [
                        'id' => $this->primaryKey(),
                        'title' => $this->string(255),
                        'elementId' => $this->integer(),
                        'siteId' => $this->integer(),
                        'dateCreated' => $this->dateTime()->notNull(),
                        'dateUpdated' => $this->dateTime()->notNull(),
                        'uid' => $this->char(36)->notNull()->defaultValue('0'),
                    ]
                )
                ->execute()
            ;

            $db->createCommand()
                ->addForeignKey(
                    $tableNameStd.'_elementId_fk',
                    $tableName,
                    ['elementId'],
                    '{{%elements}}',
                    'id',
                    'cascade'
                )
                ->execute()
            ;

            $db->createCommand()
                ->addForeignKey(
                    $tableNameStd.'_siteId_fk',
                    $tableName,
                    ['siteId'],
                    '{{%sites}}',
                    'id',
                    'cascade'
                )
                ->execute()
            ;
        }
    }
}
