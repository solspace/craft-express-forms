<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\exceptions\ExpressFormsException;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProviderInterface;

class DynamicNotificationsDecorator extends AbstractDecorator
{
    const DYNAMIC_NOTIFICATIONS_KEY = 'dynamicNotifications';

    const LOG_CATEGORY = 'Dynamic Notifications';

    /** @var LoggerProviderInterface */
    private $logger;

    /** @var EmailNotificationsProviderInterface */
    private $notifications;

    /**
     * ReturnUrlExpressFormDecorator constructor.
     *
     * @param LoggerProviderInterface             $logger
     * @param EmailNotificationsProviderInterface $notifications
     */
    public function __construct(LoggerProviderInterface $logger, EmailNotificationsProviderInterface $notifications)
    {
        $this->logger        = $logger;
        $this->notifications = $notifications;
    }

    /**
     * @return array
     */
    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_FORM_COMPLETED, [$this, 'sendEmails']],
        ];
    }

    /**
     * @param FormCompletedEvent $event
     */
    public function sendEmails(FormCompletedEvent $event)
    {
        $form = $event->getForm();
        if ($form->isMarkedAsSpam() || $form->isSkipped()) {
            return;
        }

        $dynamicNotifications = $form->getParameters()->get(self::DYNAMIC_NOTIFICATIONS_KEY);

        if (null === $dynamicNotifications) {
            return;
        }

        $to       = $dynamicNotifications['to'] ?? [];
        $template = $dynamicNotifications['template'] ?? null;

        if (empty($to) || empty($template)) {
            return;
        }

        try {
            $notification = $this->notifications->getNotification($template);
            $recipients   = StringHelper::extractSeparatedValues(is_array($to) ? implode(',', $to) : $to);

            $this->notifications->sendEmail(
                $recipients,
                $notification,
                $event->getForm(),
                $event->getSubmission(),
                $_POST
            );
        } catch (ExpressFormsException $e) {
            $this->logger->get(self::LOG_CATEGORY)->error($e->getMessage());
        }
    }
}
