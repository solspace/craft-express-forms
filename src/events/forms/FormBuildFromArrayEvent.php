<?php

namespace Solspace\ExpressForms\events\forms;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\models\Form;

class FormBuildFromArrayEvent extends CancelableEvent
{
    /** @var Form */
    public $form;

    /** @var array */
    public $data;

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
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return FormBuildFromArrayEvent
     */
    public function setData(array $data): FormBuildFromArrayEvent
    {
        $this->data = $data;

        return $this;
    }
}
