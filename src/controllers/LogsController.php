<?php

namespace Solspace\ExpressForms\controllers;

use craft\helpers\FileHelper;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use yii\web\Response;

class LogsController extends Controller
{
    /**
     * @return Response
     */
    public function actionClear(): Response
    {
        $this->requirePostRequest();
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);

        $logFilePath = ExpressFormsLogger::getLogfilePath();
        if (file_exists($logFilePath)) {
            FileHelper::unlink($logFilePath);
        }

        if (\Craft::$app->request->getIsAjax()) {
            return $this->asJson(['success' => true]);
        }

        return $this->redirect('/');
    }
}
