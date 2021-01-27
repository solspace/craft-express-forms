<?php

namespace Solspace\ExpressForms\resources\bundles;

use Solspace\Commons\Resources\CpAssetBundle;

abstract class BaseExpressFormsBundle extends CpAssetBundle
{
    protected function getSourcePath(): string
    {
        return '@Solspace/ExpressForms/resources';
    }
}
