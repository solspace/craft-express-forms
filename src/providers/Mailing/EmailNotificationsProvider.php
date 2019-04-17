<?php

namespace Solspace\ExpressForms\providers\Mailing;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;

class EmailNotificationsProvider implements EmailNotificationsProviderInterface
{
    /**
     * @param string $fileName
     *
     * @return EmailNotification
     * @throws NotificationTemplateFolderNotSetException
     */
    public function getNotification(string $fileName): EmailNotification
    {
        return ExpressForms::getInstance()->emailNotifications->getNotification($fileName);
    }

    /**
     * @param array             $recipients
     * @param EmailNotification $notification
     * @param Form              $form
     * @param Submission        $submission
     * @param array             $postedData
     *
     * @return bool
     */
    public function sendEmail(
        array $recipients,
        EmailNotification $notification,
        Form $form,
        Submission $submission,
        array $postedData
    ): bool {
        return ExpressForms::getInstance()->emailNotifications->sendEmail(
            $recipients,
            $notification,
            $form,
            $submission,
            $postedData
        );
    }

}
