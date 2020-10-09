<?php

namespace Solspace\ExpressForms\objects\Form;

use Twig\Markup;

class Recaptcha
{
    /** @var string */
    private $key;

    /** @var string */
    private $theme;

    /** @var array */
    private $errors = [];

    /** @var bool */
    private $rendered = false;

    /**
     * Recaptcha constructor.
     *
     * @param string $key
     * @param string $theme
     */
    public function __construct(string $key = null, string $theme = 'light')
    {
        $this->key = $key;
        $this->theme = $theme;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (null === $this->key) {
            return '';
        }

        $this->rendered = true;

        return '<div class="g-recaptcha" data-sitekey="' . $this->key . '" data-theme="' . $this->theme . '"></div>';
    }

    /**
     * @return Markup
     */
    public function render(): Markup
    {
        return new Markup($this->__toString(), 'utf-8');
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * @param string $message
     *
     * @return Recaptcha
     */
    public function addError(string $message): Recaptcha
    {
        if (!in_array($message, $this->errors, true)) {
            $this->errors[] = $message;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isRendered(): bool
    {
        return $this->rendered;
    }
}
