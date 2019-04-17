<?php

namespace Solspace\ExpressForms\controllers;

use Craft;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\events\emailNotifications\SaveEmailNotificationEvent;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationNotFound;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\resources\bundles\EmailNotificationEdit;
use Symfony\Component\PropertyAccess\PropertyAccess;
use yii\web\Response;

class EmailNotificationsController extends Controller
{
    const EVENT_BEFORE_SAVE_NOTIFICATION = 'beforeSaveNotification';
    const EVENT_AFTER_SAVE_NOTIFICATION  = 'afterSaveNotification';

    /**
     * @param string $fileName
     *
     * @return Response
     */
    public function actionEdit(string $fileName): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);
        $notification = ExpressForms::getInstance()->emailNotifications->getNotification($fileName);

        return $this->renderEditForm($notification, 'Edit Template');
    }

    /**
     * @return Response
     */
    public function actionCreate(): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);

        $path = ExpressForms::getInstance()->settings->getSettingsModel()->getEmailNotificationsPath();
        if (!$path) {
            throw new NotificationTemplateFolderNotSetException('No email notification templates folder set.');
        }

        $notification = EmailNotification::create($path);

        return $this->renderEditForm($notification, 'Create a new Notification Template');
    }

    /**
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);

        $fileName = Craft::$app->request->post('notification');

        $path = ExpressForms::getInstance()->settings->getSettingsModel()->getEmailNotificationsPath();
        if (!$path) {
            throw new NotificationTemplateFolderNotSetException('No email notification templates folder set.');
        }

        $notification = EmailNotification::fromFile($path . '/' . $fileName);

        if ($notification) {
            Craft::$app->getSession()->setNotice(ExpressForms::t('Notification deleted successfully'));
            unlink($path . '/' . $fileName);

            return $this->asJson(['success' => true]);
        }

        return $this->asErrorJson(ExpressForms::t('Could not delete notification'));
    }

    /**
     * @return Response
     * @throws NotificationTemplateFolderNotSetException
     */
    public function actionSave(): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SETTINGS);

        $path = ExpressForms::getInstance()->settings->getSettingsModel()->getEmailNotificationsPath();
        if (!$path) {
            throw new NotificationTemplateFolderNotSetException('No email notification templates folder set.');
        }

        $post             = Craft::$app->request->post();
        $originalFilename = $post['id'] ?? null;

        $title        = 'Edit Notification';
        try {
            $notification = ExpressForms::getInstance()->emailNotifications->getNotification($originalFilename);
        } catch (NotificationNotFound $exception) {
            $title        = 'Create a new Notification Template';
            $notification = EmailNotification::create($path);
        }

        unset($post[Craft::$app->config->general->csrfTokenName], $post['id'], $post['action']);

        $propertyAccess = PropertyAccess::createPropertyAccessor();
        foreach ($post as $key => $value) {
            if ($propertyAccess->isWritable($notification, $key)) {
                $propertyAccess->setValue($notification, $key, $value);
            }
        }

        $event = new SaveEmailNotificationEvent($notification);
        $this->trigger(self::EVENT_BEFORE_SAVE_NOTIFICATION, $event);

        $notification->validate();
        if (!$event->isValid || $notification->hasErrors()) {
            return $this->renderEditForm($notification, $title);
        }

        $writeSuccessful   = $notification->writeToFile($path . '/' . $notification->getFileName());
        $isFilenameChanged = $originalFilename !== $notification->getFileName();
        if ($writeSuccessful && $isFilenameChanged) {
            @unlink($path . '/' . $originalFilename);
        }

        $this->trigger(self::EVENT_AFTER_SAVE_NOTIFICATION, new SaveEmailNotificationEvent($notification));

        return $this->redirect('express-forms/settings/email-notifications/');
    }

    /**
     * @param EmailNotification $notification
     * @param string            $title
     *
     * @return Response
     */
    private function renderEditForm(EmailNotification $notification, string $title): Response
    {
        EmailNotificationEdit::register($this->view);
        $settings = ExpressForms::getInstance()->settings;

        $selectedSettingHandle = Craft::$app->request->getSegment(3);

        return $this->renderTemplate(
            'express-forms/settings/_components/email-notifications/edit',
            [
                'settings'       => $settings->getSettingsModel(),
                'sidebarItems'   => $settings->getSidebarItems(),
                'selectedHandle' => $selectedSettingHandle,
                'notification'   => $notification,
                'actionButton'   => null,
                'templateTitle'  => ExpressForms::t($title),
            ]
        );
    }
}
