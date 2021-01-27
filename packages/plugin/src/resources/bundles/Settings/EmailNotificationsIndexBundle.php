<?php

namespace Solspace\ExpressForms\resources\bundles\Settings;

use Solspace\ExpressForms\resources\bundles\BaseExpressFormsBundle;

class EmailNotificationsIndexBundle extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return [
            'js/scripts/settings/email-notifications/index.js',
        ];
    }
}
