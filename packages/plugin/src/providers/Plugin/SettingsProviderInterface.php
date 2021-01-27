<?php

namespace Solspace\ExpressForms\providers\Plugin;

use yii\base\Model;

interface SettingsProviderInterface
{
    public function get(): Model;
}
