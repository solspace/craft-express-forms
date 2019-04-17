<?php

namespace Solspace\ExpressForms\resources\bundles;

class IntegrationsIndex extends BaseExpressFormsBundle
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
            'js/control-panel/integrations/index.js',
        ];
    }
}
