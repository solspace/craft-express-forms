<?php

namespace Solspace\ExpressForms\resources\bundles\Resources;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class CommunityBundle extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return ['js/scripts/resources/community.js'];
    }
}
