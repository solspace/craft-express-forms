<?php

namespace Solspace\ExpressForms\events\forms;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\Form;

class FormRedirectEvent extends CancelableEvent
{
    /** @var string */
    private $redirectUrl;

    /** @var Form */
    private $form;

    /** @var Submission */
    private $submission;

    /** @var array */
    private $postData;

    /**
     * FormRedirectEvent constructor.
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

    public function setRedirectUrl(string $url = null): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
