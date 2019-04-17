<?php

namespace Solspace\ExpressForms\providers\View;

class RenderProvider implements RenderProviderInterface
{
    /**
     * @param string $string
     * @param array  $variables
     *
     * @return string
     */
    public function renderString(string $string, array $variables = []): string
    {
        return \Craft::$app->getView()->renderString($string, $variables);
    }

    /**
     * @param string $string
     * @param mixed  $object
     * @param array  $variables
     *
     * @return string
     */
    public function renderObjectTemplate(string $string, $object, array $variables = []): string
    {
        return \Craft::$app->getView()->renderObjectTemplate($string, $object, $variables);
    }
}
