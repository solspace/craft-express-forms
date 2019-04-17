<?php

namespace Solspace\ExpressForms\events\emailNotifications;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\models\EmailNotification;

class SaveEmailNotificationEvent extends CancelableEvent
{
    /** @var EmailNotification */
    private $emailNotification;

    /**
     * SaveEmailNotificationEvent constructor.
     *
     * @param EmailNotification $notification
     */
    public function __construct(EmailNotification $notification)
    {
        $this->emailNotification = $notification;

        parent::__construct();
    }

    /**
     * @return EmailNotification
     */
    public function getEmailNotification(): EmailNotification
    {
        return $this->emailNotification;
    }
}
