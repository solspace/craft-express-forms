<?php

namespace Solspace\ExpressForms\controllers;

use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\resources\bundles\SubmissionsEdit;
use Solspace\ExpressForms\resources\bundles\SubmissionsIndex;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SubmissionsController extends Controller
{
    /**
     * @param string|null $form
     *
     * @return Response
     */
    public function actionIndex(string $form = null): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SUBMISSIONS);
        SubmissionsIndex::register($this->view);

        return $this->renderTemplate(
            'express-forms/submissions/index',
            [
                'selectedFormHandle' => $form,
            ]
        );
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function actionEdit(int $id): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SUBMISSIONS);
        SubmissionsEdit::register($this->view);

        $submission = ExpressForms::getInstance()->submissions->getSubmissionById($id);

        if (!$submission) {
            throw new HttpException(
                404,
                ExpressForms::t('Submission with ID {id} not found', ['id' => $id])
            );
        }

        return $this->renderTemplate(
            'express-forms/submissions/edit',
            [
                'submission' => $submission,
                'form'       => $submission->getForm(),
            ]
        );
    }

    public function actionSave()
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SUBMISSIONS);

        $request = \Craft::$app->request;

        $id         = $request->post('id');
        $submission = ExpressForms::getInstance()->submissions->getSubmissionById($id);

        if (!$submission) {
            throw new NotFoundHttpException(ExpressForms::t('Submission not found'));
        }

        $submission->title = $request->post('title', $submission->title);
        foreach ($submission->getForm()->getFields() as $field) {
            $submission->setFieldValue($field->getHandle(), $request->post($field->getHandle()));
        }

        if (\Craft::$app->getElements()->saveElement($submission)) {
            // Return JSON response if the request is an AJAX request
            if ($request->isAjax) {
                return $this->asJson(['success' => true]);
            }

            \Craft::$app->session->setNotice(ExpressForms::t('Submission updated'));
            \Craft::$app->session->setFlash(ExpressForms::t('Submission updated'));

            return $this->redirectToPostedUrl($submission);
        }

        // Return JSON response if the request is an AJAX request
        if ($request->isAjax) {
            return $this->asJson(['success' => false]);
        }

        \Craft::$app->session->setError(ExpressForms::t('Submission could not be updated'));

        // Send the event back to the template
        \Craft::$app->urlManager->setRouteParams(
            [
                'submission' => $submission,
                'errors'     => $submission->getErrors(),
            ]
        );
    }
}
