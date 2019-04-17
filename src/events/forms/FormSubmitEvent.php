<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormSubmitEvent extends Event
{
    /** @param Form */
    private $form;

    /** @var array */
    private $submittedData;

    /**
     * FormSubmitEvent constructor.
     *
     * @param Form  $form
     * @param array $submittedData
     */
    public function __construct(Form $form, array $submittedData)
    {
        $this->form          = $form;
        $this->submittedData = $submittedData;

        parent::__construct();
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
     * @return FormSubmitEvent
     */
    public function setSubmittedData(array $submittedData): FormSubmitEvent
    {
        $this->submittedData = $submittedData;

        return $this;
    }
}
