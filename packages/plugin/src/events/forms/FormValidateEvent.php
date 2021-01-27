<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormValidateEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var array */
    private $submittedData;

    /**
     * FormValidateEvent constructor.
     */
    public function __construct(Form $form, array $submittedData)
    {
        $this->form = $form;
        $this->submittedData = $submittedData;

        parent::__construct([]);
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getSubmittedData(): array
    {
        return $this->submittedData;
    }

    public function setSubmittedData(array $submittedData): self
    {
        $this->submittedData = $submittedData;

        return $this;
    }
}
