<?php

namespace Solspace\ExpressForms\resources\bundles;

class FormIndex extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getStylesheets(): array
    {
        return ['fonts/fonts.css'];
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'js/control-panel/forms/index.js',
        ];
    }
}
