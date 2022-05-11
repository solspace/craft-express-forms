<?php

namespace Solspace\ExpressForms\controllers;

use Craft;
use craft\web\Controller;
use Solspace\ExpressForms\events\forms\FormAjaxResponseEvent;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\events\forms\FormInvalidEvent;
use Solspace\ExpressForms\events\forms\FormRedirectEvent;
use Solspace\ExpressForms\events\forms\FormSubmitEvent;
use Solspace\ExpressForms\ExpressForms;
use yii\web\Response;

class SubmitController extends Controller
{
    public const EVENT_REDIRECT = 'redirect';
    public const EVENT_FORM_COMPLETED = 'formCompleted';
    public const EVENT_FORM_INVALID = 'formInvalid';
    public const EVENT_BEFORE_FORM_SUBMIT = 'beforeFormSubmit';
    public const EVENT_BEFORE_AJAX_RESPONSE = 'beforeAjaxResponse';
    public const EVENT_BEFORE_AJAX_ERROR_RESPONSE = 'beforeAjaxErrorResponse';

    public array|int|bool $allowAnonymous = true;

    public function actionIndex(): ?Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->request;

        $uuid = $request->post('formId');
        $form = ExpressForms::getInstance()->forms->getFormByUuid($uuid);

        $acceptType = $request->getHeaders()->get('Accept');
        if (null !== $acceptType && '*/*' !== $acceptType) {
            $isJsonResponse = str_contains($acceptType, 'application/json');
        } else {
            $isJsonResponse = $request->getIsAjax();
        }

        if ($form) {
            $postData = $request->post();

            $event = new FormSubmitEvent($form, $postData);
            $this->trigger(self::EVENT_BEFORE_FORM_SUBMIT, $event);

            $form->submit($event->getSubmittedData());

            if ($form->isSuccess()) {
                $submissionsService = ExpressForms::getInstance()->submissions;

                $submission = $submissionsService->buildSubmission($form, $postData);
                $submissionsService->saveSubmission($submission);

                $this->trigger(self::EVENT_FORM_COMPLETED, new FormCompletedEvent($form, $submission, $postData));

                $event = new FormRedirectEvent($form, $submission, $postData);
                $this->trigger(self::EVENT_REDIRECT, $event);
                $redirectUrl = $event->getRedirectUrl();

                if ($isJsonResponse) {
                    $ajaxResponseData = [
                        'success' => true,
                        'submissionId' => $submission->id ?: null,
                        'returnUrl' => $redirectUrl,
                        'errors' => [],
                    ];

                    $event = new FormAjaxResponseEvent($form, $submission, $ajaxResponseData);
                    $this->trigger(self::EVENT_BEFORE_AJAX_RESPONSE, $event);

                    return $this->asJson($event->getAjaxResponseData());
                }

                if ($event->isValid && $redirectUrl) {
                    return Craft::$app->getResponse()->redirect($event->getRedirectUrl());
                }

                return Craft::$app->getResponse()->redirect($request->getUrl());
            }

            $this->trigger(self::EVENT_FORM_INVALID, new FormInvalidEvent($form, $postData));

            if ($isJsonResponse) {
                $fieldErrors = [];
                foreach ($form->getFields() as $field) {
                    if ($field->hasErrors()) {
                        $fieldErrors[$field->getHandle()] = $field->getErrors();
                    }
                }

                $ajaxResponseData = [
                    'success' => false,
                    'returnUrl' => null,
                    'formErrors' => $form->getErrors(),
                    'errors' => $fieldErrors,
                ];

                $event = new FormAjaxResponseEvent($form, null, $ajaxResponseData);
                $this->trigger(self::EVENT_BEFORE_AJAX_ERROR_RESPONSE, $event);

                return $this->asJson($event->getAjaxResponseData());
            }
        }

        return null;
    }
}
