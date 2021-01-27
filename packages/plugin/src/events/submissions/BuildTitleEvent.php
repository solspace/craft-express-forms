<?php

namespace Solspace\ExpressForms\events\submissions;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class BuildTitleEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var string */
    private $title;

    /** @var array */
    private $twigVariables;

    /**
     * BuildTitleEvent constructor.
     */
    public function __construct(Form $form, string $title, array $twigVariables)
    {
        $this->form = $form;
        $this->title = $title;
        $this->twigVariables = $twigVariables;

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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
     */
    public function setTwigVariables($twigVariables): self
    {
        $this->twigVariables = $twigVariables;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addTwigVariable(string $key, $value): self
    {
        $this->twigVariables[$key] = $value;

        return $this;
    }
}
