<?php

namespace Solspace\ExpressForms\resources\bundles;

class SubmissionsEdit extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'js/control-panel/submissions/edit.js',
        ];
    }
}
