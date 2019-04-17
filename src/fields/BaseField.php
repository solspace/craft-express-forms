<?php

namespace Solspace\ExpressForms\fields;

use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use Solspace\ExpressForms\events\fields\FieldSetValueEvent;
use yii\base\Event;
use yii\db\Schema;

abstract class BaseField extends Field implements FieldInterface, PreviewableFieldInterface
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $handle;

    /** @var bool */
    public $required;

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $valid = true;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValueAsString();
    }

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return ['required'];
    }

    /**
     * @return bool
     */
    public function hasMultipleValues(): bool
    {
        return $this instanceof MultipleValueInterface;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValueAsString(): string
    {
        $value = $this->getValue();

        if (is_object($value) || is_array($value)) {
            $value = implode(', ', (array) $value);
        }

        return $value ?? '';
    }

    /**
     * @param $value
     *
     * @return FieldInterface
     */
    public function setValue($value): FieldInterface
    {
        $event = new FieldSetValueEvent($this, $value);
        Event::trigger($this, self::EVENT_BEFORE_SET_VALUE, $event);

        if (!$event->isValid) {
            return $this;
        }

        $this->value = $event->getValue();

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Alias for ::getName()
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->getName();
    }

    /**
     * @return string|null
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return (bool) $this->required;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->getErrors());
    }

    /**
     * @param string $message
     *
     * @return FieldInterface
     */
    public function addValidationError(string $message): FieldInterface
    {
        $existingErrors = parent::getErrors(self::VALIDATION_ERROR_KEY) ?? [];
        if (!in_array($message, $existingErrors, true)) {
            $this->addError(self::VALIDATION_ERROR_KEY, $message);
        }

        return $this;
    }

    /**
     * @param bool $validationErrorsOnly
     *
     * @return array|string
     */
    public function getErrors($validationErrorsOnly = true)
    {
        $errors = parent::getErrors();

        if ($validationErrorsOnly) {
            return $errors[self::VALIDATION_ERROR_KEY] ?? [];
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getErrorsAsString(): string
    {
        return implode(', ', $this->getErrors());
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $items = [
            'id'       => $this->getId(),
            'uid'      => $this->getUid(),
            'name'     => $this->getName(),
            'handle'   => $this->getHandle(),
            'type'     => $this->getType(),
            'required' => $this->isRequired(),
        ];

        foreach ($this->getSettings() as $key => $value) {
            $items[$key] = $value;
        }

        return $items;
    }
}
