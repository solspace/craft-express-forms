<?php

namespace Solspace\ExpressForms\events\forms;

use craft\events\CancelableEvent;
use craft\web\Response;
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
     * @param string|null $url
     *
     * @return FormRedirectEvent
     */
    public function setRedirectUrl(string $url = null): FormRedirectEvent
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
