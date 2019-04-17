<?php

namespace Solspace\ExpressForms\events\submissions;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class BuildTitleEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var array */
    private $twigVariables;

    /**
     * BuildTitleEvent constructor.
     *
     * @param Form  $form
     * @param array $twigVariables
     */
    public function __construct(Form $form, array $twigVariables)
    {
        $this->form          = $form;
        $this->twigVariables = $twigVariables;

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
     * @return mixed
     */
    public function getTwigVariables()
    {
        return $this->twigVariables;
    }

    /**
     * @param mixed $twigVariables
     *
     * @return BuildTitleEvent
     */
    public function setTwigVariables($twigVariables): BuildTitleEvent
    {
        $this->twigVariables = $twigVariables;

        return $this;
    }
}
