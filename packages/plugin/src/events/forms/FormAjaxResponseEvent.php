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
     * @param Submission $submission
     */
    public function __construct(Form $form, Submission $submission = null, array $ajaxResponseData = [])
    {
        $this->form = $form;
        $this->submission = $submission;
        $this->ajaxResponseData = $ajaxResponseData;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setForm(Form $form): self
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return null|Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @param Submission $submission
     */
    public function setSubmission(Submission $submission = null): self
    {
        $this->submission = $submission;

        return $this;
    }

    public function getAjaxResponseData(): array
    {
        return $this->ajaxResponseData;
    }

    public function setAjaxResponseData(array $ajaxResponseData): self
    {
        $this->ajaxResponseData = $ajaxResponseData;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addAjaxResponseData(string $key, $value): self
    {
        $this->ajaxResponseData[$key] = $value;

        return $this;
    }
}
