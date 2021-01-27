<?php

namespace Solspace\ExpressForms\fields;

use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\validators\HandleValidator;
use Solspace\ExpressForms\events\fields\FieldSetValueEvent;
use yii\base\Event;

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

    public function __toString(): string
    {
        return $this->getValueAsString();
    }

    public function settingsAttributes(): array
    {
        return ['required'];
    }

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

    public function getValueAsString(): string
    {
        $value = $this->getValue();

        if (\is_object($value) || \is_array($value)) {
            $value = implode(', ', (array) $value);
        }

        return $value ?? '';
    }

    /**
     * @param $value
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
     * @return null|int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Alias for ::getName().
     *
     * @return null|string
     */
    public function getLabel()
    {
        return $this->getName();
    }

    /**
     * @return null|string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function isRequired(): bool
    {
        return (bool) $this->required;
    }

    public function isValid(): bool
    {
        return empty($this->getErrors());
    }

    public function addValidationError(string $message): FieldInterface
    {
        $existingErrors = parent::getErrors(self::VALIDATION_ERROR_KEY) ?? [];
        if (!\in_array($message, $existingErrors, true)) {
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
            'id' => $this->getId(),
            'uid' => $this->getUid(),
            'name' => $this->getName(),
            'handle' => $this->getHandle(),
            'type' => $this->getType(),
            'required' => $this->isRequired(),
        ];

        foreach ($this->getSettings() as $key => $value) {
            $items[$key] = $value;
        }

        return $items;
    }

    /**
     * Returns the validators applicable to the current [[scenario]].
     *
     * @param string $attribute the name of the attribute whose applicable validators should be returned.
     *                          If this is null, the validators for ALL attributes in the model will be returned.
     *
     * @return \yii\validators\Validator[] the validators applicable to the current [[scenario]]
     */
    public function getActiveValidators($attribute = null)
    {
        $validators = parent::getActiveValidators($attribute);

        foreach ($validators as $validator) {
            if ($validator instanceof HandleValidator) {
                $validator::$baseReservedWords = [];
                $validator->reservedWords = [];
            }
        }

        return $validators;
    }
}
