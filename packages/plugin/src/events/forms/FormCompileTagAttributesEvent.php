<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\ParameterBag;
use yii\base\Event;

class FormCompileTagAttributesEvent extends Event
{
    /** @var Form */
    private $form;

    /**
     * FormCompileTagAttributesEvent constructor.
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getAttributes(): ParameterBag
    {
        return $this->getForm()->getHtmlAttributes();
    }
}
