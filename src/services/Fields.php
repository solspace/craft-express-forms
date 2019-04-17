<?php

namespace Solspace\ExpressForms\services;

use Craft;
use craft\base\Field as CraftField;
use craft\models\FieldLayoutTab;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\records\FormRecord;

class Fields extends BaseService
{
    /**
     * @param Form $form
     *
     * @return bool
     */
    public function updateFieldLayout(Form $form): bool
    {
        $db            = Craft::$app->db;
        $fieldsService = Craft::$app->getFields();

        $context = Submission::getFieldContextName($form);

        Craft::$app->getContent()->contentTable = Submission::getContentTableName($form);
        Craft::$app->getContent()->fieldContext = $context;

        $toDeleteIds = [];

        /** @var CraftField $craftField */
        foreach ($fieldsService->getAllFields($context) as $craftField) {
            $toDeleteIds[] = $craftField->id;
        }

        $savableFields = $savableFieldIds = [];

        $sortOrder = 1;
        foreach ($form->getFields() as $formField) {
            $id = $formField->getId();

            if ($id && in_array($id, $toDeleteIds, false)) {
                $index = array_search($id, $toDeleteIds, false);
                unset($toDeleteIds[$index]);
            }

            $formField->sortOrder = $sortOrder++;
            $fieldsService->saveField($formField);

            if ($formField->id) {
                $savableFields[]   = $formField;
                $savableFieldIds[] = $formField->id;
            }
        }

        foreach ($toDeleteIds as $id) {
            $fieldsService->deleteFieldById($id);
        }

        $layout = $form->getFieldLayout();
        if (!$layout) {
            $layout       = Craft::$app->fields->assembleLayout(['Default' => $savableFieldIds]);
            $layout->type = Form::class;
        } else {
            $tab            = new FieldLayoutTab();
            $tab->name      = 'Default';
            $tab->sortOrder = 1;
            $tab->setFields($savableFields);

            $layout->setTabs([$tab]);
            $layout->setFields($savableFields);
        }

        Craft::$app->fields->saveLayout($layout);

        if ($layout->id && !$form->getFieldLayoutId()) {
            $form->setFieldLayoutId($layout->id);
            $db->createCommand()
                ->update(
                    FormRecord::TABLE,
                    ['fieldLayoutId' => $layout->id],
                    ['id' => $form->getId()]
                )
                ->execute();
        }

        return true;
    }
}
