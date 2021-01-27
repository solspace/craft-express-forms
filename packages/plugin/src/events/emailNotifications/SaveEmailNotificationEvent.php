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
     */
    public function __construct(EmailNotification $notification)
    {
        $this->emailNotification = $notification;

        parent::__construct();
    }

    public function getEmailNotification(): EmailNotification
    {
        return $this->emailNotification;
    }
}
