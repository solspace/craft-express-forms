<?php

namespace Solspace\ExpressForms\services;

use craft\helpers\App;
use craft\mail\Message;
use DateTime;
use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\emailNotifications\RenderEmailValuesEvent;
use Solspace\ExpressForms\events\emailNotifications\SendEmailEvent;
use Solspace\ExpressForms\events\forms\FormCompletedEvent;
use Solspace\ExpressForms\exceptions\EmailNotifications\EmailNotificationsException;
use Solspace\ExpressForms\exceptions\EmailNotifications\NotificationTemplateFolderNotSetException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class EmailNotifications extends BaseService
{
    public const EVENT_BEFORE_RENDER = 'beforeRender';
    public const EVENT_BEFORE_SEND = 'beforeSend';
    public const EVENT_AFTER_SEND = 'afterSend';

    /**
     * @return EmailNotification[]
     */
    public function getNotifications(): array
    {
        $notifications = [];
        $settings = $this->getSettingsService()->getSettingsModel();

        $path = $settings->getEmailNotificationsPath();
        if (!$path || !file_exists($path) || !is_dir($path)) {
            return [];
        }

        $finder = new Finder();

        /** @var SplFileInfo[] $files */
        $files = $finder
            ->name('*.twig')
            ->name('*.html')
            ->files()
            ->ignoreDotFiles(true)
            ->in($path)
        ;

        foreach ($files as $file) {
            try {
                $template = EmailNotification::fromFile($file->getRealPath());

                $notifications[] = $template;
            } catch (EmailNotificationsException $exception) {
                $this
                    ->getLogger(ExpressFormsLogger::EMAIL_NOTIFICATION)
                    ->error(
                        ExpressForms::t(
                            'Malformed email notification template "{filename}" in "{path}"',
                            [
                                'filename' => $file->getBasename(),
                                'path' => $file->getRealPath(),
                            ]
                        )
                    )
                ;
            }
        }

        return $notifications;
    }

    public function getNotification(string $fileName): EmailNotification
    {
        $settings = $this->getSettingsService()->getSettingsModel();

        $path = $settings->getEmailNotificationsPath();
        if (!$path || !file_exists($path) || !is_dir($path)) {
            throw new NotificationTemplateFolderNotSetException(
                ExpressForms::t('Email notification template folder not set')
            );
        }

        return EmailNotification::fromFile($path.'/'.$fileName);
    }

    public function sendAdminNotifications(FormCompletedEvent $event)
    {
        $form = $event->getForm();
        if ($form->isMarkedAsSpam() || $form->isSkipped()) {
            return;
        }

        $formIsValid = $form->isSuccess() && $form->isValid();
        if ($formIsValid && $form->getAdminNotification()) {
            $notification = $this->getEmailNotification($form->getAdminNotification());
            if ($notification) {
                $validEmails = $this->validateEmails(StringHelper::extractSeparatedValues($form->getAdminEmails() ?? ''), true);
                if ($validEmails) {
                    $this->sendEmail(
                        $validEmails,
                        $notification,
                        $form,
                        $event->getSubmission(),
                        $event->getPostData()
                    );
                }
            }
        }
    }

    public function sendEmailNotifications(FormCompletedEvent $event)
    {
        $form = $event->getForm();
        if ($form->isMarkedAsSpam() || $form->isSkipped()) {
            return;
        }

        $formIsValid = $form->isSuccess() && $form->isValid();
        if ($formIsValid && $form->getSubmitterNotification() && $form->getSubmitterEmailField()) {
            $notification = $this->getEmailNotification($form->getSubmitterNotification());
            if ($notification) {
                $emailFieldUid = $form->getSubmitterEmailField();

                $field = $form->getFields()->get($emailFieldUid);
                if ($field) {
                    $this->sendEmail(
                        StringHelper::extractSeparatedValues($field->getValue() ?? ''),
                        $notification,
                        $form,
                        $event->getSubmission(),
                        $event->getPostData()
                    );
                }
            }
        }
    }

    public function sendEmail(
        array $recipients,
        EmailNotification $notification,
        Form $form,
        Submission $submission,
        array $postedData
    ): bool {
        $logger = ExpressFormsLogger::getInstance(ExpressFormsLogger::EMAIL_NOTIFICATION);

        $templateVariables = $this->getTemplateVariables($form, $submission, $postedData);
        $fieldValues = $form->getFields()->asArray();

        $renderEvent = new RenderEmailValuesEvent($form, $submission, $notification, $templateVariables, $fieldValues);

        $this->trigger(self::EVENT_BEFORE_RENDER, $renderEvent);
        $templateVariables = $renderEvent->getTemplateVariables();

        $fromName = $this->renderString($notification->getFromName(), $fieldValues, $templateVariables);
        $fromEmail = $this->renderString($notification->getFromEmail(), $fieldValues, $templateVariables);

        $cc = $notification->getCc();
        if ($cc) {
            $cc = $this->renderString($cc, $fieldValues, $templateVariables);
            if (\is_string($cc)) {
                $cc = StringHelper::extractSeparatedValues($cc);
            }
        }

        $bcc = $notification->getBcc();
        if ($bcc) {
            $bcc = $this->renderString($bcc, $fieldValues, $templateVariables);
            if (\is_string($bcc)) {
                $bcc = StringHelper::extractSeparatedValues($bcc);
            }
        }

        $email = new Message();

        try {
            $email->variables = $templateVariables;
            $email
                ->setTo($recipients)
                ->setFrom([$fromEmail => $fromName])
                ->setSubject($this->renderString($notification->getSubject(), $fieldValues, $templateVariables))
                ->setHtmlBody($this->renderString($notification->getBody(), $fieldValues, $templateVariables))
            ;

            if ($cc) {
                $email->setCc($cc);
            }

            if ($bcc) {
                $email->setBcc($bcc);
            }

            if ($notification->getReplyTo()) {
                $email->setReplyTo(
                    $this->renderString($notification->getReplyTo(), $fieldValues, $templateVariables)
                );
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = 'Email notification ['.$notification->getFileName().']: '.$message;

            $logger->error($message);

            return false;
        }

        if ($notification->isIncludeAttachments()) {
            foreach ($form->getFields() as $field) {
                if (!$field instanceof File || !$field->getHandle()) {
                    continue;
                }

                $assetIds = $field->getValue() ?? [];
                foreach ($assetIds as $assetId) {
                    $asset = \Craft::$app->assets->getAssetById((int) $assetId);
                    if ($asset) {
                        $email->attach($asset->getTransformSource());
                    }
                }
            }
        }

        try {
            $sendEmailEvent = new SendEmailEvent($email, $form, $notification, $submission, $templateVariables);
            $this->trigger(self::EVENT_BEFORE_SEND, $sendEmailEvent);

            if (!$sendEmailEvent->isValid) {
                return false;
            }

            \Craft::$app->mailer->send($email);

            $this->trigger(self::EVENT_AFTER_SEND, $sendEmailEvent);

            return true;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $message = 'Email notification ['.$notification->getFileName().']: '.$message;

            $logger->error($message);
        }

        return false;
    }

    private function getEmailNotification(string $filename): ?EmailNotification
    {
        try {
            return ExpressForms::getInstance()->emailNotifications->getNotification($filename);
        } catch (EmailNotificationsException $exception) {
            ExpressFormsLogger::getInstance(ExpressFormsLogger::EMAIL_NOTIFICATION)
                ->error($exception->getMessage())
            ;
        }

        return null;
    }

    private function getTemplateVariables(Form $form, Submission $submission, array $postedValues): array
    {
        return [
            'form' => $form,
            'submission' => $submission,
            'postedValues' => $postedValues,
            'dateCreated' => new DateTime(),
        ];
    }

    private function renderString(string $string, array $fieldValues, array $templateVariables): bool|string|null
    {
        $view = \Craft::$app->view;

        $value = $view->renderObjectTemplate($string, $fieldValues, $templateVariables);
        if (preg_match('/^\$(\w+)$/', $value)) {
            $value = App::parseEnv($value);
        }

        return $value;
    }

    private function validateEmails(mixed $emails, bool $parseEnv = false): bool|array
    {
        if (!$emails) {
            return false;
        }

        $validEmails = [];

        foreach ($emails as $email) {
            if ($parseEnv && preg_match('/^\$(\w+)$/', $email)) {
                $email = App::parseEnv($email);
            }

            if (filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = $email;
            }
        }

        return $validEmails;
    }
}
