<?php

namespace Solspace\ExpressForms\decorators;

use Solspace\Commons\Translators\TranslatorInterface;
use Solspace\ExpressForms\ExpressForms;

abstract class AbstractTranslatableDecorator extends AbstractDecorator
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * AbstractTranslatableDecorator constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $message
     * @param array  $variables
     *
     * @return string
     */
    protected function translate(string $message, array $variables = []): string
    {
        return $this->translator->translate(ExpressForms::TRANSLATION_CATEGORY, $message, $variables);
    }
}
