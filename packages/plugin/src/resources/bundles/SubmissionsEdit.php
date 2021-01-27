<?php

namespace Solspace\ExpressForms\resources\bundles;

class SubmissionsEdit extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return [
            'js/scripts/submissions/edit.js',
        ];
    }
}
