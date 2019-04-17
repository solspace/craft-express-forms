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
     *
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;

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
     * @return ParameterBag
     */
    public function getAttributes(): ParameterBag
    {
        return $this->getForm()->getHtmlAttributes();
    }
}
