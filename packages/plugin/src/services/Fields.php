<?php

namespace Solspace\ExpressForms\services;

use Craft;
use craft\base\Field as CraftField;
use craft\fieldlayoutelements\CustomField;
use craft\helpers\StringHelper;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\records\FormRecord;

class Fields extends BaseService
{
    public function updateFieldLayout(Form $form): bool
    {
        $db = Craft::$app->db;
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

            if ($id && \in_array($id, $toDeleteIds, false)) {
                $index = array_search($id, $toDeleteIds, false);
                unset($toDeleteIds[$index]);
            }

            $formField->sortOrder = $sortOrder++;
            $fieldsService->saveField($formField);

            if ($formField->id) {
                $savableFields[] = $formField;
            }
        }

        foreach ($toDeleteIds as $id) {
            $fieldsService->deleteFieldById($id);
        }

        $layoutPost = [
            'uid' => $form->getFieldLayout()?->uid ?? StringHelper::UUID(),
            'tabs' => [
                [
                    'name' => 'Default',
                    'uid' => StringHelper::UUID(),
                    'elements' => array_map(
                        fn (FieldInterface $field) => [
                            'type' => CustomField::class,
                            'label' => $field->getName(),
                            'instructions' => null,
                            'tip' => null,
                            'warning' => null,
                            'required' => $field->isRequired(),
                            'width' => 100,
                            'uid' => StringHelper::UUID(),
                            'fieldUid' => $field->getUid(),
                        ],
                        $savableFields,
                    ),
                ],
            ],
        ];

        Craft::$app->request->setBodyParams(['express-forms.fieldLayout' => json_encode($layoutPost)]);
        $_POST['express-forms.fieldLayout'] = $layoutPost;

        $layout = Craft::$app->fields->assembleLayoutFromPost('express-forms.');
        $layout->type = Form::class;

        Craft::$app->fields->saveLayout($layout);

        if ($layout->id && !$form->getFieldLayoutId()) {
            $form->setFieldLayoutId($layout->id);
            $db->createCommand()
                ->update(
                    FormRecord::TABLE,
                    ['fieldLayoutId' => $layout->id],
                    ['id' => $form->getId()]
                )
                ->execute()
            ;
        }

        return true;
    }
}
