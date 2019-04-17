<?php

namespace Solspace\ExpressForms\events\settings;

use craft\events\CancelableEvent;

class SaveSettingsEvent extends CancelableEvent
{
    /** @var array */
    private $settingsData = [];

    /** @var array */
    private $errors = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->settingsData;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return SaveSettingsEvent
     */
    public function addData(string $key, $value): SaveSettingsEvent
    {
        $this->settingsData[$key] = $value;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return SaveSettingsEvent
     */
    public function setData(array $data = []): SaveSettingsEvent
    {
        $this->settingsData = $data;

        return $this;
    }

    /**
     * @param string $handle
     * @param string $message
     *
     * @return SaveSettingsEvent
     */
    public function addError(string $handle, string $message): SaveSettingsEvent
    {
        if (!isset($this->errors[$handle])) {
            $this->errors[$handle] = [];
        }

        if (!in_array($message, $this->errors[$handle], true)) {
            $this->errors[$handle][] = $message;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
