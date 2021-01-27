<?php

namespace Solspace\ExpressForms\resources\bundles;

class OverviewStatsWidgetBundle extends BaseExpressFormsBundle
{
    public function getScripts(): array
    {
        return [
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js',
        ];
    }
}
