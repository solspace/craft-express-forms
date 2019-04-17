<?php

namespace Solspace\ExpressForms\resources\bundles;

class EmailNotificationEdit extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'lib/ace/ace.js',
            'lib/ace/mode-html.js',
            'lib/ace/theme-github.js',
        ];
    }
}
