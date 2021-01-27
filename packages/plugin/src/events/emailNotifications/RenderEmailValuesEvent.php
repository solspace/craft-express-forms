<?php

namespace Solspace\ExpressForms\events\emailNotifications;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\EmailNotification;
use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class RenderEmailValuesEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var Submission */
    private $submission;

    /** @var EmailNotification */
    private $notification;

    /** @var array */
    private $templateVariables;

    /** @var array */
    private $fieldValues;

    /**
     * RenderEmailValuesEvent constructor.
     */
    public function __construct(
        Form $form,
        Submission $submission,
        EmailNotification $notification,
        array $templateVariables,
        array $fieldValues
    ) {
        $this->form = $form;
        $this->submission = $submission;
        $this->notification = $notification;
        $this->templateVariables = $templateVariables;
        $this->fieldValues = $fieldValues;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    public function getNotification(): EmailNotification
    {
        return $this->notification;
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

    public function getFieldValues(): array
    {
        return $this->fieldValues;
    }

    public function setFieldValues(array $fieldValues): self
    {
        $this->fieldValues = $fieldValues;

        return $this;
    }
}
