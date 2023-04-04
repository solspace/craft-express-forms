<?php

namespace Solspace\ExpressForms\controllers;

use craft\helpers\StringHelper;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\Commons\Loggers\Readers\LineLogReader;
use Solspace\ExpressForms\exceptions\Form\FormsNotFoundException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\FieldCollection;
use Solspace\ExpressForms\objects\Collections\IntegrationMappingCollection;
use Solspace\ExpressForms\records\FormRecord;
use Solspace\ExpressForms\resources\bundles\Builder;
use Solspace\ExpressForms\resources\bundles\FormIndex;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FormsController extends Controller
{
    public function init(): void
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_FORMS);

        parent::init();
    }

    public function actionIndex(): Response
    {
        FormIndex::register($this->view);

        $forms = ExpressForms::getInstance()->forms->getAllForms();
        $exportTypes = ExpressForms::getInstance()->export->getExportTypes();

        $errorLogCount = null;
        if (ExpressForms::getInstance()->getSettings()->showErrorLogBanner) {
            $logReader = new LineLogReader(ExpressFormsLogger::getLogfilePath());
            $errorLogCount = $logReader->count();
        }

        return $this->renderTemplate(
            'express-forms/forms/index',
            [
                'forms' => $forms,
                'exportTypes' => $exportTypes,
                'errorLogCount' => $errorLogCount,
            ]
        );
    }

    public function actionEdit(string $handle): Response
    {
        $form = ExpressForms::getInstance()->forms->getFormByHandle($handle);
        if (null === $form) {
            throw new FormsNotFoundException(ExpressForms::t('Form not found'));
        }

        return $this->createEditTemplate($form);
    }

    public function actionCreate(): Response
    {
        return $this->createEditTemplate(new Form());
    }

    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $post = json_decode(\Craft::$app->request->getRawBody(), true);
        unset($post['id']);

        $formFactory = ExpressForms::container()->formFactory();
        $formSerializer = ExpressForms::container()->formSerializer();
        $fieldFactory = ExpressForms::container()->fieldFactory();
        $mappingFactory = ExpressForms::container()->integrationMappingFactory();

        $form = ExpressForms::getInstance()->forms->getFormByUuid($post['uuid'] ?? null);
        if (!$form) {
            $form = new Form();
        }

        $form = $formFactory->populateFromArray($form, $post);

        $fieldCollection = new FieldCollection();

        // TODO: do not store fields in the form table anymore
        $postedFields = $post['fields'] ?? [];
        foreach ($postedFields as $field) {
            $fieldCollection->addField($fieldFactory->fromArray($field));
        }
        $form->setFields($fieldCollection);
        $form->setIntegrations($mappingFactory->fromArray($form, $post['integrations'] ?? []));

        $response = ExpressForms::getInstance()->forms->save($form);

        $jsonData = [
            'success' => $form->getId() && empty($response->getErrors()),
            'errors' => $response->getErrors(),
            'data' => $formSerializer->toArray($form),
        ];

        return $this->asJson($jsonData);
    }

    public function actionDuplicate(): Response
    {
        $this->requirePostRequest();

        if (\Craft::$app->request->isAjax) {
            $uuid = \Craft::$app->request->post('uuid');
        } else {
            $post = json_decode(\Craft::$app->request->getRawBody(), true);
            $uuid = $post['uuid'];
        }

        if (!empty($uuid)) {
            $form = ExpressForms::getInstance()->forms->getFormByUuid($uuid);
            if ($form) {
                $formFactory = ExpressForms::container()->formFactory();
                $formSerializer = ExpressForms::container()->formSerializer();
                $fieldFactory = ExpressForms::container()->fieldFactory();
                $fieldSerializer = ExpressForms::container()->fieldSerializer();

                if (!empty($post)) {
                    // Request came from Form Edit -> Save as new form
                    $serializedForm = $post;
                } else {
                    // Request came from Form Cards -> Duplicate
                    $serializedForm = $formSerializer->toArray($form);
                }

                $newForm = $formFactory->populateFromArray(new Form(), $serializedForm);
                $newForm->setId();
                $newForm->setUuid(StringHelper::UUID());
                $newForm->setIntegrations(new IntegrationMappingCollection());

                $fieldCollection = new FieldCollection();

                foreach ($form->getFields() as $field) {
                    $uid = $field->getUid();
                    $clone = $fieldSerializer->toArray($field);
                    $clone['uid'] = StringHelper::UUID();

                    if ($uid && $form->getSubmitterEmailField() === $uid) {
                        $newForm->setSubmitterEmailField($clone['uid']);
                    }

                    $fieldCollection->addField($fieldFactory->fromArray($clone));
                }
                $newForm->setFields($fieldCollection);
                $newForm->setHandle(FormRecord::getUniqueHandle($newForm->getHandle()));

                $response = ExpressForms::getInstance()->forms->save($newForm);

                if ($response->getErrors()) {
                    return $this->asErrorJson(implode(',', $response->getErrors()));
                }

                $jsonData = [
                    'errors' => null,
                    'success' => true,
                    'data' => $formSerializer->toArray($newForm),
                ];

                return $this->asJson($jsonData);
            }

            return $this->asErrorJson(ExpressForms::t('Could not find form'));
        }

        throw new NotFoundHttpException();
    }

    public function actionResetSpam(): Response
    {
        $this->requirePostRequest();

        if (\Craft::$app->request->isAjax) {
            $uuid = \Craft::$app->request->post('uuid');
            if ($uuid) {
                \Craft::$app->db
                    ->createCommand()
                    ->update(
                        FormRecord::TABLE,
                        ['spamCount' => 0],
                        ['uuid' => $uuid]
                    )
                    ->execute()
                ;
            }

            return $this->asJson(['success' => true]);
        }

        throw new NotFoundHttpException();
    }

    public function actionSort(): Response
    {
        $this->requirePostRequest();

        if (\Craft::$app->request->isAjax) {
            $order = \Craft::$app->request->post('order', []);

            foreach ($order as $index => $id) {
                \Craft::$app->db->createCommand()
                    ->update(
                        FormRecord::TABLE,
                        ['sortOrder' => $index + 1],
                        ['id' => $id]
                    )
                    ->execute()
                ;
            }

            return $this->asJson(['success' => true]);
        }

        throw new NotFoundHttpException();
    }

    public function actionDelete(): Response
    {
        $this->requirePostRequest();

        if (\Craft::$app->request->isAjax) {
            $id = \Craft::$app->request->post('id');

            if (ExpressForms::getInstance()->forms->deleteById($id)) {
                return $this->asJson(['success' => true]);
            }
        }

        throw new NotFoundHttpException();
    }

    private function createEditTemplate(Form $form): Response
    {
        Builder::register($this->view);

        $formJson = ExpressForms::container()->formSerializer()->toJson($form);

        $volumes = [];
        foreach (\Craft::$app->volumes->getAllVolumes() as $volume) {
            $volumes[] = [
                'value' => (int) $volume->id,
                'label' => $volume->name,
            ];
        }

        $fileKinds = [];
        foreach (ExpressForms::container()->getFileTypeProvider()->getFileKinds() as $kind => $extensions) {
            $fileKinds[] = $kind;
        }

        $notifications = ExpressForms::getInstance()->emailNotifications->getNotifications();

        \Craft::$app->view->registerTranslations(
            ExpressForms::TRANSLATION_CATEGORY,
            $this->getBuilderTranslationKeys()
        );

        return $this->renderTemplate(
            'express-forms/forms/edit',
            [
                'form' => $form,
                'formJson' => $formJson,
                'fileKinds' => $fileKinds,
                'volumes' => $volumes,
                'notifications' => $notifications,
                'integrations' => ExpressForms::getInstance()->integrations->getIntegrationMetadata(),
                'enhancedUi' => ExpressForms::getInstance()->settings->getSettingsModel()->enhancedUI,
            ]
        );
    }

    private function getBuilderTranslationKeys(): array
    {
        return [
            'Form saved successfully',
            'Choose a mailing list to connect and map form submissions to.',
            'Choose a resource for this CRM integration to map form submissions to.',
            'Quick Save',
            'Save and finish',
            'Save and add another',
            'Save as a new form',
            'Form Settings',
            'Control and set the basic settings for your form here.',
            'The name you see for form in the control panel, an…se in templates and email notification templates.',
            'Name',
            'Used for calling the form inside a template.',
            'Handle',
            'An internal note explaining the purpose of the form, and also available for use in templates.',
            'Description',
            'Used for styling form card in CP as well as differ…idgets, etc. Also available for use in templates.',
            'Color',
            'The generated title for the submission, similar to…e Freeform fields, e.g. "{firstName} {lastName}".',
            'Submission Title',
            'Do you want save submissions for this form to the database?',
            'Save Submissions',
            'Notifications',
            'This area allows you to manage email notifications for your form.',
            'Select the email notification template that should be used for Admin email notifications.',
            'Admin Notification',
            'Select template...',
            'Email Notification Template',
            'Email Notification Template EEE',
            'SSS',
            'Select the email notification template that should… email notification to the submitter of the form.',
            'Submitter Notification',
            'Select the Email field in your form that will contain the email address of the submitter.',
            'Submitter Email',
            'Select...',
            'Email',
            'Integrations',
            'With Express Forms Pro edition, you\'ll see options… API integrations when you have at least 1 setup.',
            'Refresh',
            'Mailing List',
            'Another List',
            'General Interest',
            'First Name',
            'Last Name',
            'Subject',
            'Message',
            'How did you hear about us?',
            'Attachment',
            'Accept Terms',
            'Fields & Layout',
            'Add field',
            'Required',
            'Textarea',
            'Options',
            'Checkbox',
            'Hidden',
            'File',
            'Text',
            'Select upload location...',
            'Max file count',
            'Max file size (KB)',
            'Restrict file types',
        ];
    }
}
