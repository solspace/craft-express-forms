<?php

namespace Solspace\ExpressForms\objects\Form;

use Twig\Markup;

class Recaptcha
{
    private array $errors = [];
    private bool $rendered = false;

    public function __construct(
        private ?string $key,
        private ?string $theme
    ) {
    }

    public function __toString(): string
    {
        if (null === $this->key) {
            return '';
        }

        $this->rendered = true;

        return '<div class="g-recaptcha" data-sitekey="'.$this->key.'" data-theme="'.$this->theme.'"></div>';
    }

    public function render(): Markup
    {
        return new Markup($this->__toString(), 'utf-8');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    public function addError(string $message): self
    {
        if (!\in_array($message, $this->errors, true)) {
            $this->errors[] = $message;
        }

        return $this;
    }

    public function isRendered(): bool
    {
        return $this->rendered;
    }
}
