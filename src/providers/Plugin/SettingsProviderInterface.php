<?php

namespace Solspace\ExpressForms\providers\Plugin;

use yii\base\Model;

interface SettingsProviderInterface
{
    /**
     * @return Model
     */
    public function get(): Model;
}
