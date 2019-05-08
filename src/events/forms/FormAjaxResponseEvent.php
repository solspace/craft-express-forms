<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormAjaxResponseEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var Submission */
    private $submission;

    /** @var array */
    private $ajaxResponseData;

    /**
     * FormAjaxResponseEvent constructor.
     *
     * @param Form       $form
     * @param Submission $submission
     * @param array      $ajaxResponseData
     */
    public function __construct(Form $form, Submission $submission = null, array $ajaxResponseData = [])
    {
        $this->form             = $form;
        $this->submission       = $submission;
        $this->ajaxResponseData = $ajaxResponseData;

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
     * @param Form $form
     *
     * @return FormAjaxResponseEvent
     */
    public function setForm(Form $form): FormAjaxResponseEvent
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Submission|null
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     *
     * @return FormAjaxResponseEvent
     */
    public function setSubmission(Submission $submission = null): FormAjaxResponseEvent
    {
        $this->submission = $submission;

        return $this;
    }

    /**
     * @return array
     */
    public function getAjaxResponseData(): array
    {
        return $this->ajaxResponseData;
    }

    /**
     * @param array $ajaxResponseData
     *
     * @return FormAjaxResponseEvent
     */
    public function setAjaxResponseData(array $ajaxResponseData): FormAjaxResponseEvent
    {
        $this->ajaxResponseData = $ajaxResponseData;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return FormAjaxResponseEvent
     */
    public function addAjaxResponseData(string $key, $value): FormAjaxResponseEvent
    {
        $this->ajaxResponseData[$key] = $value;

        return $this;
    }
}
