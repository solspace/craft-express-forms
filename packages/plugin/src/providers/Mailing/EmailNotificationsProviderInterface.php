<?php

namespace Solspace\ExpressForms\providers\Mailing;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;

interface EmailNotificationsProviderInterface
{
    /**
     * @throws NotificationTemplateFolderNotSetException
     */
    public function getNotification(string $fileName): EmailNotification;

    public function sendEmail(
        array $recipients,
        EmailNotification $notification,
        Form $form,
        Submission $submission,
        array $postedData
    ): bool;
}
