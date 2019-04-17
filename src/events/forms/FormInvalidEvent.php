<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormInvalidEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var array */
    private $postData;

    /**
     * FormCompletedEvent constructor.
     *
     * @param Form       $form
     * @param array      $postData
     */
    public function __construct(Form $form, array $postData)
    {
        $this->form       = $form;
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
     * @return array
     */
    public function getPostData(): array
    {
        return $this->postData;
    }

    /**
     * @param array $postData
     *
     * @return FormInvalidEvent
     */
    public function setPostData(array $postData): FormInvalidEvent
    {
        $this->postData = $postData;

        return $this;
    }
}
