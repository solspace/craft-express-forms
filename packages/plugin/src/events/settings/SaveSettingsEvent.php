<?php

namespace Solspace\ExpressForms\events\settings;

use craft\events\CancelableEvent;

class SaveSettingsEvent extends CancelableEvent
{
    /** @var array */
    private $settingsData = [];

    /** @var array */
    private $errors = [];

    public function getData(): array
    {
        return $this->settingsData;
    }

    /**
     * @param mixed $value
     */
    public function addData(string $key, $value): self
    {
        $this->settingsData[$key] = $value;

        return $this;
    }

    public function setData(array $data = []): self
    {
        $this->settingsData = $data;

        return $this;
    }

    public function addError(string $handle, string $message): self
    {
        if (!isset($this->errors[$handle])) {
            $this->errors[$handle] = [];
        }

        if (!\in_array($message, $this->errors[$handle], true)) {
            $this->errors[$handle][] = $message;
        }

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
