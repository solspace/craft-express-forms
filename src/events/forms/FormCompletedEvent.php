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
     *
     * @param Form       $form
     * @param Submission $submission
     * @param array      $postData
     */
    public function __construct(Form $form, Submission $submission, array $postData)
    {
        $this->form       = $form;
        $this->submission = $submission;
        $this->postData   = $postData;

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
     * @return Submission
     */
    public function getSubmission(): Submission
    {
        return $this->submission;
    }

    /**
     * @return array
     */
    public function getPostData(): array
    {
        return $this->postData;
    }

    /**
     * @param array $postData
     *
     * @return FormCompletedEvent
     */
    public function setPostData(array $postData): FormCompletedEvent
    {
        $this->postData = $postData;

        return $this;
    }
}
