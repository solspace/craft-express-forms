<?php

namespace Solspace\ExpressForms\events\emailNotifications;

use craft\events\CancelableEvent;
use craft\mail\Message;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;

class SendEmailEvent extends CancelableEvent
{
    /** @var Message */
    private $email;

    /** @var Form */
    private $form;

    /** @var EmailNotification */
    private $notification;

    /** @var Submission */
    private $submission;

    /** @var array */
    private $templateVariables;

    /**
     * SendEmailEvent constructor.
     *
     * @param Message           $email
     * @param Form              $form
     * @param EmailNotification $notification
     * @param Submission        $submission
     * @param array             $templateVariables
     */
    public function __construct(
        Message $email,
        Form $form,
        EmailNotification $notification,
        Submission $submission,
        array $templateVariables
    ) {
        $this->email             = $email;
        $this->form              = $form;
        $this->notification      = $notification;
        $this->submission        = $submission;
        $this->templateVariables = $templateVariables;

        parent::__construct();
    }

    /**
     * @return Message
     */
    public function getEmail(): Message
    {
        return $this->email;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return EmailNotification
     */
    public function getNotification(): EmailNotification
    {
        return $this->notification;
    }

    /**
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * @return array
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * @param array $templateVariables
     *
     * @return SendEmailEvent
     */
    public function setTemplateVariables(array $templateVariables): SendEmailEvent
    {
        $this->templateVariables = $templateVariables;

        return $this;
    }
}
