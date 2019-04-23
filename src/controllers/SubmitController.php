<?php

namespace Solspace\ExpressForms\controllers;

use Craft;
use craft\web\Controller;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\events\forms\FormInvalidEvent;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\ExpressForms;
use yii\web\Response;

class SubmitController extends Controller
{
    const EVENT_REDIRECT           = 'redirect';
    const EVENT_FORM_COMPLETED     = 'formCompleted';
    const EVENT_FORM_INVALID       = 'formInvalid';
    const EVENT_BEFORE_FORM_SUBMIT = 'beforeFormSubmit';

    public $allowAnonymous = true;

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $this->requirePostRequest();
        $uuid = Craft::$app->request->post('formId');
        $form = ExpressForms::getInstance()->forms->getFormByUuid($uuid);

        $isAjax = Craft::$app->request->isAjax;

        if ($form) {
            $postData = Craft::$app->request->post();
            $form->submit($postData);

            if ($form->isSuccess()) {
                $submissionsService = ExpressForms::getInstance()->submissions;

                $submission = $submissionsService->buildSubmission($form, $postData);
                $submissionsService->saveSubmission($submission);

                $this->trigger(self::EVENT_FORM_COMPLETED, new FormCompletedEvent($form, $submission, $postData));

                $event = new FormRedirectEvent($form, $submission, $postData);
                $this->trigger(self::EVENT_REDIRECT, $event);
                $redirectUrl = $event->getRedirectUrl();

                if ($isAjax) {
                    return $this->asJson(
                        [
                            'success'      => true,
                            'submissionId' => $submission->id ?: null,
                            'returnUrl'    => $redirectUrl,
                            'errors'       => [],
                        ]
                    );
                }

                if ($event->isValid && $redirectUrl) {
                    return Craft::$app->getResponse()->redirect($event->getRedirectUrl());
                }
            } else {
                $this->trigger(self::EVENT_FORM_INVALID, new FormInvalidEvent($form, $postData));

                if ($isAjax) {
                    $fieldErrors = [];
                    foreach ($form->getFields() as $field) {
                        if ($field->hasErrors()) {
                            $fieldErrors[$field->getHandle()] = $field->getErrors();
                        }
                    }

                    return $this->asJson(
                        [
                            'success'    => false,
                            'returnUrl'  => null,
                            'formErrors' => $form->getErrors(),
                            'errors'     => $fieldErrors,
                        ]
                    );
                }
            }
        }
    }
}
