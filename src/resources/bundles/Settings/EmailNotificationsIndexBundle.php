<?php

namespace Solspace\ExpressForms\resources\bundles\Settings;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class EmailNotificationsIndexBundle extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'js/control-panel/settings/email-notifications/index.js',
        ];
    }
}
