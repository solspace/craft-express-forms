<?php

namespace Solspace\ExpressForms\decorators;

use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\ExpressForms;

abstract class AbstractTranslatableDecorator extends AbstractDecorator
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    protected function translate(string $message, array $variables = []): string
    {
        return $this->translator->translate(ExpressForms::TRANSLATION_CATEGORY, $message, $variables);
    }
}
