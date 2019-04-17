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
     *
     * @param Form  $form
     * @param array $submittedData
     */
    public function __construct(Form $form, array $submittedData)
    {
        $this->form          = $form;
        $this->submittedData = $submittedData;

        parent::__construct([]);
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getSubmittedData(): array
    {
        return $this->submittedData;
    }

    /**
     * @param array $submittedData
     *
     * @return FormValidateEvent
     */
    public function setSubmittedData(array $submittedData): FormValidateEvent
    {
        $this->submittedData = $submittedData;

        return $this;
    }
}
