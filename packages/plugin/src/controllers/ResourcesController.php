<?php

namespace Solspace\ExpressForms\controllers;

use craft\web\Controller;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\resources\bundles\Resources\CommunityBundle;
use Solspace\ExpressForms\resources\bundles\Resources\ExploreBundle;
use yii\web\Response;

class ResourcesController extends Controller
{
    public function actionCommunity(): Response
    {
        CommunityBundle::register(\Craft::$app->getView());

        return $this->renderTemplate('express-forms/resources/community', []);
    }

    public function actionExplore(): Response
    {
        ExploreBundle::register(\Craft::$app->getView());

        return $this->renderTemplate(
            'express-forms/resources/explore',
            [
                'isPro' => ExpressForms::getInstance()->isPro(),
            ]
        );
    }

    public function actionSupport(): Response
    {
        CommunityBundle::register(\Craft::$app->getView());

        return $this->renderTemplate('express-forms/resources/support', []);
    }
}
