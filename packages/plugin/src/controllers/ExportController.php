<?php

namespace Solspace\ExpressForms\controllers;

use craft\db\Query;
use craft\db\Table;
use craft\web\Controller;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\export\BuildExportQueryEvent;
use Solspace\ExpressForms\events\export\CompileExportableFields;
use Solspace\ExpressForms\exceptions\Form\FormsNotFoundException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\objects\Export\DateField;
use Solspace\ExpressForms\objects\Export\ExportField;
use Solspace\ExpressForms\objects\Export\ExportFieldInterface;
use Solspace\ExpressForms\objects\Export\IntField;
use Solspace\ExpressForms\objects\Export\StringField;
use Solspace\ExpressForms\records\FormRecord;
use yii\web\Response;

class ExportController extends Controller
{
    public const EVENT_COMPILE_EXPORTABLE_FIELDS = 'compileExportableFields';
    public const EVENT_BUILD_QUERY = 'buildQuery';

    public function actionIndex(): Response
    {
        $this->requirePostRequest();
        $request = \Craft::$app->request;

        $formsService = ExpressForms::getInstance()->forms;
        $exportService = ExpressForms::getInstance()->export;

        $id = $request->post('id');
        $type = $request->post('type');

        $form = $formsService->getFormByUuid($id);
        if (!$form) {
            throw new FormsNotFoundException('Could not find form');
        }

        /** @var ExportFieldInterface[] $fields */
        $fields = [
            new IntField('s.[[id]]', 'ID'),
            new DateField('s.[[dateCreated]]', 'Date Created'),
            new StringField('c.[[title]]', 'Title'),
        ];

        foreach ($form->getFields() as $field) {
            $fields[] = ExportField::createFromField($field);
        }

        $event = new CompileExportableFields($fields);
        $this->trigger(self::EVENT_COMPILE_EXPORTABLE_FIELDS, $event);

        $select = [];
        foreach ($event->getFields() as $field) {
            $select[] = $field->getHandle();
        }

        $query = (new Query())
            ->select($select)
            ->from(Submission::TABLE.' s')
            ->innerJoin(Submission::getContentTableName($form).'c', 'c.[[elementId]] = s.[[id]]')
            ->innerJoin(FormRecord::TABLE.' f', 'f.[[id]] = s.[[formId]]')
            ->innerJoin(Table::ELEMENTS.' e', 'e.[[id]] = s.[[id]] AND e.[[dateDeleted]] IS NULL')
            ->where(
                [
                    's.[[formId]]' => $form->getId(),
                    'c.[[siteId]]' => \Craft::$app->sites->currentSite->id,
                ]
            )
        ;

        $event = new BuildExportQueryEvent($query);
        $this->trigger(self::EVENT_BUILD_QUERY, $event);

        $result = $event->getQuery()->all();

        $data = [];
        foreach ($result as $row) {
            $normalized = [];
            $index = 0;
            foreach ($row as $key => $value) {
                $field = $fields[$index++];

                $normalized[$field->getLabel()] = $field->transformValue($value);
            }

            $data[] = $normalized;
        }

        $response = \Craft::$app->response;
        $exportService->exportSubmissions($type, $form, $data, $response);

        return $response;
    }
}
