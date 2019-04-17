<?php

namespace Solspace\ExpressForms\resources\bundles;

use Solspace\Commons\Resources\CpAssetBundle;

abstract class BaseExpressFormsBundle extends CpAssetBundle
{
    /**
     * @return string
     */
    protected function getSourcePath(): string
    {
        return '@Solspace/ExpressForms/resources';
    }
}
