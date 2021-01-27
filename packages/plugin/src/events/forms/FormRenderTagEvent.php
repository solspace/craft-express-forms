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
     */
    public function __construct(Form $form, string $output)
    {
        $this->form = $form;
        $this->setOutput($output);

        parent::__construct();
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getOutput(): string
    {
        return implode("\n", $this->outputChunks);
    }

    public function setOutput(string $output): self
    {
        $this->outputChunks = [$output];

        return $this;
    }

    public function appendToOutput(string $chunk): self
    {
        $this->outputChunks[] = $chunk;

        return $this;
    }

    public function prependToOutput(string $chunk): self
    {
        array_unshift($this->outputChunks, $chunk);

        return $this;
    }
}
