<?php

namespace Solspace\ExpressForms\providers\View;

class RenderProvider implements RenderProviderInterface
{
    public function renderString(string $string, array $variables = []): string
    {
        return \Craft::$app->getView()->renderString($string, $variables);
    }

    /**
     * @param mixed $object
     */
    public function renderObjectTemplate(string $string, $object, array $variables = []): string
    {
        return \Craft::$app->getView()->renderObjectTemplate($string, $object, $variables);
    }
}
