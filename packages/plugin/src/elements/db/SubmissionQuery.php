<?php

namespace Solspace\ExpressForms\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\records\FormRecord;

class SubmissionQuery extends ElementQuery
{
    public ?int $formId = null;
    public ?string $form = null;
    public ?int $incrementalId = null;

    public function formId(int $value): self
    {
        $this->formId = $value;

        return $this;
    }

    public function form(mixed $value): self
    {
        $this->form = $value;

        return $this;
    }

    public function incrementalId(int $value): self
    {
        $this->incrementalId = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        static $formHandleToIdMap;
        static $formIdToHandleMap;

        if (null === $formHandleToIdMap) {
            $result = (new Query())
                ->select(['id', 'handle'])
                ->from(FormRecord::TABLE)
                ->all()
            ;

            $formHandleToIdMap = array_column($result, 'id', 'handle');
            $formHandleToIdMap = array_map('intval', $formHandleToIdMap);
            $formIdToHandleMap = array_flip($formHandleToIdMap);
        }

        $selectedFormHandle = null;
        if (!$this->formId && $this->id) {
            $formIds = (new Query())
                ->select(['formId'])
                ->from(Submission::TABLE)
                ->where(Db::parseParam('id', $this->id))
                ->column()
            ;

            $this->formId = 1 === \count($formIds) ? $formIds[0] : $formIds;
        }

        $this->contentTable = null;
        if ($this->formId && is_numeric($this->formId)) {
            $form = $formIdToHandleMap[$this->formId] ?? null;
            if ($form) {
                $this->contentTable = Submission::getContentTableNameFromHandle($form);
            }
        }

        $table = Submission::TABLE_STD;
        $formTable = FormRecord::TABLE_STD;
        $this->joinElementTable($table);

        $this->query->innerJoin(FormRecord::TABLE.' '.$formTable, "{$formTable}.[[id]] = {$table}.[[formId]]");
        $this->subQuery->innerJoin(FormRecord::TABLE.' '.$formTable, "{$formTable}.[[id]] = {$table}.[[formId]]");

        $select = [
            $table.'.[[formId]]',
            $table.'.[[incrementalId]]',
            $formTable.'.[[name]] as formName',
        ];

        $this->query->select($select);
        $this->subQuery->select($select);

        $formHandle = $this->form;
        if ($formHandle instanceof Form) {
            $formHandle = $formHandle->getHandle();
        }

        if ($formHandle && $formHandleToIdMap[$formHandle]) {
            $this->formId = $formHandleToIdMap[$formHandle];
        }

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam($table.'.[[formId]]', $this->formId));
        }

        if ($this->incrementalId) {
            $this->subQuery->andWhere(Db::parseParam($table.'.[[incrementalId]]', $this->incrementalId));
        }

        return parent::beforePrepare();
    }

    /**
     * {@inheritdoc}
     */
    protected function customFields(): array
    {
        $formService = ExpressForms::getInstance()->forms;
        if (null === $this->formId) {
            return [];
        }

        $form = $formService->getFormById($this->formId);

        if ($form) {
            return $form->getFields()->asArray();
        }

        return [];
    }
}
