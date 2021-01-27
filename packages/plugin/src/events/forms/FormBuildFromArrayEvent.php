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

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
