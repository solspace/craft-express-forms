<?php

namespace Solspace\ExpressForms\events\forms;

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
     */
    public function __construct(Form $form, array $postData)
    {
        $this->form = $form;
        $this->postData = $postData;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
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
