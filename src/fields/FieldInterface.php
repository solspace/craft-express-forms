<?php

namespace Solspace\ExpressForms\fields;

interface FieldInterface extends \JsonSerializable
{
    const VALIDATION_ERROR_KEY   = 'expressformsValidationErrors';
    const EVENT_BEFORE_SET_VALUE = 'beforeSetValue';

    const TYPE_TEXT     = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_OPTIONS  = 'options';
    const TYPE_EMAIL    = 'email';
    const TYPE_HIDDEN   = 'hidden';
    const TYPE_FILE     = 'file';

    /**
     * @return bool
     */
    public function hasMultipleValues(): bool;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return string
     */
    public function getValueAsString(): string;

    /**
     * @param $value
     *
     * @return $this
     */
    public function setValue($value): FieldInterface;

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getUid(): string;

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return string|null
     */
    public function getHandle();

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @param string $message
     *
     * @return FieldInterface
     */
    public function addValidationError(string $message): FieldInterface;

    /**
     * @return string
     */
    public function getErrorsAsString(): string;
}
