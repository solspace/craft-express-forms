<?php

namespace Solspace\ExpressForms\resources\bundles;

class Builder extends BaseExpressFormsBundle
{
    public function getStylesheets(): array
    {
        return ['fonts/fonts.css'];
    }

    public function getScripts(): array
    {
        return [
            'js/builder/vendor.js',
            'js/builder/builder.js',
        ];
    }
}
