<?php

namespace Solspace\ExpressForms\resources\bundles\Resources;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class CommunityBundle extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return ['js/control-panel/resources/community.js'];
    }
}
