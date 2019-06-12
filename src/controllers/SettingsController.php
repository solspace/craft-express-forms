<?php

namespace Solspace\ExpressForms\controllers;

use Craft;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Settings;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
    /**
     * @return Response
     */
    public function actionIndex(): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);
        if (!Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            throw new ForbiddenHttpException('Administrative changes are disallowed in this environment.');
        }

        return $this->renderEditTemplate();
    }

    /**
     * @return Response
     */
    public function actionSave(): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);
        $this->requirePostRequest();

        if (ExpressForms::getInstance()->settings->saveSettings()) {
            \Craft::$app->session->setNotice(ExpressForms::t('Settings updated'));

            return $this->redirectToPostedUrl();
        }

        \Craft::$app->session->setError(ExpressForms::t('Settings could not be updated'));

        return $this->renderEditTemplate();
    }

    /**
     * @return Response
     */
    private function renderEditTemplate(): Response
    {
        $settingsService = ExpressForms::getInstance()->settings;
        $selectedHandle  = \Craft::$app->request->getSegment(3);

        /** @var Settings $settings */
        $settings = ExpressForms::getInstance()->getSettings();
        $items    = $settingsService->getSidebarItems();

        if (null === $selectedHandle) {
            reset($items);
            $selectedHandle = key($items);
        }

        $event = $settingsService->onRenderSettings($selectedHandle);

        return $this->renderTemplate(
            'express-forms/settings',
            [
                'settings'        => $settings,
                'sidebarItems'    => $items,
                'selectedHandle'  => $selectedHandle,
                'settingsTitle'   => $event->getTitle(),
                'settingsContent' => $event->getContent(),
                'actionButton'    => $event->getActionButton(),
            ]
        );
    }
}
