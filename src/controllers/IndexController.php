<?php

namespace Solspace\ExpressForms\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\ExpressForms;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        $map = [
            ExpressForms::PERMISSION_FORMS => 'express-forms/forms',
            ExpressForms::PERMISSION_SUBMISSIONS => 'express-forms/submissions',
            ExpressForms::PERMISSION_SETTINGS => 'express-forms/settings',
            ExpressForms::PERMISSION_RESOURCES => 'express-forms/resources',
        ];

        foreach ($map as $permission => $url) {
            if (PermissionHelper::checkPermission($permission)) {
                return $this->redirect(UrlHelper::cpUrl($url));
            }
        }

        return $this->renderTemplate('express-forms');
    }
}
