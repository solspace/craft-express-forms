<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormCompletedEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var Submission */
    private $submission;

    /** @var array */
    private $postData;

    /**
     * FormCompletedEvent constructor.
     */
    public function __construct(Form $form, Submission $submission, array $postData)
    {
        $this->form = $form;
        $this->submission = $submission;
        $this->postData = $postData;

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

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function setPostData(array $postData): self
    {
        $this->postData = $postData;

        return $this;
    }
}
