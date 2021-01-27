<?php

namespace Solspace\ExpressForms\providers\Plugin;

use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\models\Settings;
use yii\base\Model;

class SettingsProvider implements SettingsProviderInterface
{
    /**
     * @return Model|Settings
     */
    public function get(): Model
    {
        return ExpressForms::getInstance()->getSettings();
    }
}
