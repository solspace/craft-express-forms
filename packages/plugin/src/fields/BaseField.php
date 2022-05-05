<?php

namespace Solspace\ExpressForms\fields;

use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\validators\HandleValidator;
use Solspace\ExpressForms\events\fields\FieldSetValueEvent;
use yii\base\Event;

abstract class BaseField extends Field implements FieldInterface, PreviewableFieldInterface
{
    public int|string|null $id = null;
    public ?string $name = null;
    public ?string $handle = null;
    public ?bool $required = false;

    protected mixed $value = null;
    protected bool $valid = true;

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

    public function getValue(): mixed
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

    public function setValue(mixed $value): FieldInterface
    {
        $event = new FieldSetValueEvent($this, $value);
        Event::trigger($this, self::EVENT_BEFORE_SET_VALUE, $event);

        if (!$event->isValid) {
            return $this;
        }

        $this->value = $event->getValue();

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Alias for ::getName().
     */
    public function getLabel(): ?string
    {
        return $this->getName();
    }

    public function getHandle(): ?string
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

    public function getErrors($validationErrorsOnly = true): array
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

    public function jsonSerialize(): array
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
    public function getActiveValidators($attribute = null): array
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
