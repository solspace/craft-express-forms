<?php

namespace Solspace\ExpressForms\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\Response;

class IndexController extends Controller
{
    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        return $this->redirect(UrlHelper::cpUrl('express-forms/forms'));
    }
}
