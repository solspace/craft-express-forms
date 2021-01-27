<?php

namespace Solspace\ExpressForms\resources\bundles;

class FormIndex extends BaseExpressFormsBundle
{
    public function getStylesheets(): array
    {
        return ['fonts/fonts.css'];
    }

    public function getScripts(): array
    {
        return [
            'js/scripts/forms/index.js',
        ];
    }
}
