<?php

namespace Solspace\ExpressForms\fields;

interface FieldInterface extends \JsonSerializable
{
    const VALIDATION_ERROR_KEY = 'expressformsValidationErrors';
    const EVENT_BEFORE_SET_VALUE = 'beforeSetValue';

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_OPTIONS = 'options';
    const TYPE_EMAIL = 'email';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_FILE = 'file';

    public function hasMultipleValues(): bool;

    /**
     * @return mixed
     */
    public function getValue();

    public function getValueAsString(): string;

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value): self;

    /**
     * @return null|int
     */
    public function getId();

    public function getUid(): string;

    /**
     * @return null|string
     */
    public function getName();

    /**
     * @return null|string
     */
    public function getHandle();

    /**
     * @return null|string
     */
    public function getType();

    public function isRequired(): bool;

    public function isValid(): bool;

    public function addValidationError(string $message): self;

    public function getErrorsAsString(): string;
}
