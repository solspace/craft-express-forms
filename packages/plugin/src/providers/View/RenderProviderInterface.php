<?php

namespace Solspace\ExpressForms\providers\View;

interface RenderProviderInterface
{
    public function renderString(string $string, array $variables = []): string;

    /**
     * @param mixed $object
     */
    public function renderObjectTemplate(string $string, $object, array $variables = []): string;
}
