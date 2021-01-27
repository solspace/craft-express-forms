<?php

namespace Solspace\ExpressForms\resources\bundles;

class SubmissionsIndex extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return [
            'js/scripts/submissions/index.js',
            'js/scripts/submissions/table-view.js',
            'js/scripts/submissions/element-index.js',
        ];
    }
}
