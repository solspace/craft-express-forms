<?php

namespace Solspace\ExpressForms\resources\bundles;

class SubmissionsIndex extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'js/control-panel/submissions/index.js',
            'js/control-panel/submissions/table-view.js',
            'js/control-panel/submissions/element-index.js',
        ];
    }
}
