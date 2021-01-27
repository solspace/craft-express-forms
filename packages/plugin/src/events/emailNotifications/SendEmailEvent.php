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
     */
    public function __construct(
        Message $email,
        Form $form,
        EmailNotification $notification,
        Submission $submission,
        array $templateVariables
    ) {
        $this->email = $email;
        $this->form = $form;
        $this->notification = $notification;
        $this->submission = $submission;
        $this->templateVariables = $templateVariables;

        parent::__construct();
    }

    public function getEmail(): Message
    {
        return $this->email;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getNotification(): EmailNotification
    {
        return $this->notification;
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    public function setTemplateVariables(array $templateVariables): self
    {
        $this->templateVariables = $templateVariables;

        return $this;
    }
}
