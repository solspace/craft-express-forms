<?php

namespace Solspace\ExpressForms\objects\Responses;

use Solspace\ExpressForms\models\Form;

class FormSaveResponse
{
    /** @var Form */
    private $form;

    /** @var array */
    private $errors;

    /**
     * FormSaveResponse constructor.
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }
}
