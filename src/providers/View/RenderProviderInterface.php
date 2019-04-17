<?php

namespace Solspace\ExpressForms\providers\View;

interface RenderProviderInterface
{
    /**
     * @param string $string
     * @param array  $variables
     *
     * @return string
     */
    public function renderString(string $string, array $variables = []): string;

    /**
     * @param string $string
     * @param mixed  $object
     * @param array  $variables
     *
     * @return string
     */
    public function renderObjectTemplate(string $string, $object, array $variables = []): string;
}
