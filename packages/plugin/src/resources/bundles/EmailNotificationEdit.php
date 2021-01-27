<?php

namespace Solspace\ExpressForms\resources\bundles;

class EmailNotificationEdit extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return [
            'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-twig.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/theme-xcode.min.js',
        ];
    }
}
