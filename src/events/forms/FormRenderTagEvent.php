<?php

namespace Solspace\ExpressForms\events\forms;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;

class FormRenderTagEvent extends Event
{
    /** @var Form */
    private $form;

    /** @var array */
    private $outputChunks;

    /**
     * FormRenderTagEvent constructor.
     *
     * @param Form   $form
     * @param string $output
     */
    public function __construct(Form $form, string $output)
    {
        $this->form = $form;
        $this->setOutput($output);

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
    public function getOutput(): string
    {
        return implode("\n", $this->outputChunks);
    }

    /**
     * @param string $output
     *
     * @return FormRenderTagEvent
     */
    public function setOutput(string $output): FormRenderTagEvent
    {
        $this->outputChunks = [$output];

        return $this;
    }

    /**
     * @param string $chunk
     *
     * @return FormRenderTagEvent
     */
    public function appendToOutput(string $chunk): FormRenderTagEvent
    {
        $this->outputChunks[] = $chunk;

        return $this;
    }

    /**
     * @param string $chunk
     *
     * @return FormRenderTagEvent
     */
    public function prependToOutput(string $chunk): FormRenderTagEvent
    {
        array_unshift($this->outputChunks, $chunk);

        return $this;
    }
}
