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
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
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
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    /**
     * @param array $errors
     *
     * @return FormSaveResponse
     */
    public function setErrors(array $errors): FormSaveResponse
    {
        $this->errors = $errors;

        return $this;
    }
}
