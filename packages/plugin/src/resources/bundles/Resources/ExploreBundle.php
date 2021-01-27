<?php

namespace Solspace\ExpressForms\resources\bundles\Resources;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class ExploreBundle extends BaseExpressFormsBundle
{
    public function getStylesheets(): array
    {
        return ['fonts/fonts.css'];
    }

    public function getScripts(): array
    {
        return ['js/scripts/resources/explore.js'];
    }
}
