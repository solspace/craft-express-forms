<?php

namespace Solspace\ExpressForms\resources\bundles;

class OverviewStatsWidgetBundle extends BaseExpressFormsBundle
{
    /**
     * @return array
     */
    public function getScripts(): array
    {
        return [
            'lib/chart/chart.bundle.min.js',
        ];
    }
}
