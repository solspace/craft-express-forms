<?php

namespace Solspace\ExpressForms\resources\bundles;

class IntegrationsIndex extends BaseExpressFormsBundle
{
    public function getStylesheets(): array
    {
        return ['fonts/fonts.css'];
    }

    public function getScripts(): array
    {
        return [
            'js/scripts/integrations/index.js',
        ];
    }
}
