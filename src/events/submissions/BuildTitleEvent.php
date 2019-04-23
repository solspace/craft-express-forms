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
     *
     * @param Form   $form
     * @param string $title
     * @param array  $twigVariables
     */
    public function __construct(Form $form, string $title, array $twigVariables)
    {
        $this->form          = $form;
        $this->title         = $title;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return BuildTitleEvent
     */
    public function setTitle(string $title): BuildTitleEvent
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
     *
     * @return BuildTitleEvent
     */
    public function setTwigVariables($twigVariables): BuildTitleEvent
    {
        $this->twigVariables = $twigVariables;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return BuildTitleEvent
     */
    public function addTwigVariable(string $key, $value): BuildTitleEvent
    {
        $this->twigVariables[$key] = $value;

        return $this;
    }
}
