<?php

namespace Solspace\ExpressForms\fields;

interface FieldInterface extends \JsonSerializable
{
    public const VALIDATION_ERROR_KEY = 'expressformsValidationErrors';
    public const EVENT_BEFORE_SET_VALUE = 'beforeSetValue';

    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_CHECKBOX = 'checkbox';
    public const TYPE_OPTIONS = 'options';
    public const TYPE_EMAIL = 'email';
    public const TYPE_HIDDEN = 'hidden';
    public const TYPE_FILE = 'file';

    public function hasMultipleValues(): bool;

    public function getValue(): mixed;

    public function getValueAsString(): string;

    public function setValue(mixed $value): self;

    public function getId(): ?int;

    public function getUid(): string;

    public function getName(): ?string;

    public function getHandle(): ?string;

    public function getType(): ?string;

    public function isRequired(): bool;

    public function isValid(): bool;

    public function addValidationError(string $message): self;

    public function getErrorsAsString(): string;
}
