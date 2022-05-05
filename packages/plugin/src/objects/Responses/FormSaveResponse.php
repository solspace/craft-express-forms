<?php

namespace Solspace\ExpressForms\objects\Responses;

use Solspace\ExpressForms\models\Form;

class FormSaveResponse
{
    private array $errors;

    public function __construct(private Form $form)
    {
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
