<?php

namespace Solspace\ExpressForms\decorators\Forms\Extras;

use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\controllers\SubmitController;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\exceptions\ExpressFormsException;
use Solspace\ExpressForms\providers\Logging\LoggerProviderInterface;
use Solspace\ExpressForms\providers\Mailing\EmailNotificationsProviderInterface;

class DynamicRecipientsDecorator extends AbstractDecorator
{
    public const DYNAMIC_RECIPIENTS_KEY = 'dynamicRecipients';

    public const LOG_CATEGORY = 'Dynamic Recipients';

    public function __construct(
        private LoggerProviderInterface $logger,
        private EmailNotificationsProviderInterface $notifications
    ) {
    }

    public function getEventListenerList(): array
    {
        return [
            [SubmitController::class, SubmitController::EVENT_FORM_COMPLETED, [$this, 'sendEmails']],
        ];
    }

    public function sendEmails(FormCompletedEvent $event): void
    {
        $form = $event->getForm();
        if ($form->isMarkedAsSpam() || $form->isSkipped()) {
            return;
        }

        $dynamicRecipients = $form->getParameters()->get(self::DYNAMIC_RECIPIENTS_KEY);

        if (null === $dynamicRecipients) {
            return;
        }

        foreach ($dynamicRecipients as $fieldHandle => $data) {
            if (!isset($data['map'], $data['template'])) {
                continue;
            }

            $map = $data['map'];
            $template = $data['template'];

            try {
                $notification = $this->notifications->getNotification($template);
            } catch (ExpressFormsException $e) {
                $this->logger->get(self::LOG_CATEGORY)->error($e->getMessage());

                continue;
            }

            $field = $event->getForm()->getFields()->get($fieldHandle);
            if (!$field) {
                continue;
            }

            $values = $field->getValue();
            if (!\is_array($values)) {
                $values = [$values];
            }

            $recipients = [];
            foreach ($values as $value) {
                if (\array_key_exists($value, $map)) {
                    $matchingRecipients = $map[$value];
                    if (!\is_array($matchingRecipients)) {
                        $matchingRecipients = StringHelper::extractSeparatedValues($matchingRecipients);
                    }

                    $recipients = array_merge($recipients, $matchingRecipients);
                }
            }

            if (!$recipients) {
                continue;
            }

            $this->notifications->sendEmail(
                $recipients,
                $notification,
                $event->getForm(),
                $event->getSubmission(),
                $_POST
            );
        }
    }
}
