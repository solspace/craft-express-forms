<?php

namespace Solspace\ExpressForms\resources\bundles\Resources;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class ExploreBundle extends BaseExpressFormsBundle
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
        return ['js/control-panel/resources/explore.js'];
    }
}
