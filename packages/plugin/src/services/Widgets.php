<?php
/**
 * Express Forms for Craft.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2019-2024, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/express-forms/v2/
 *
 * @license       https://docs.solspace.com/license-agreement/
 */

namespace Solspace\ExpressForms\services;

use Carbon\Carbon;
use craft\base\Component;
use Solspace\ExpressForms\exceptions\ExpressFormsException;
use Solspace\ExpressForms\ExpressForms;

class Widgets extends Component
{
    public const CHART_LINE = 'line';
    public const CHART_BAR = 'bar';
    public const CHART_DONUT = 'doughnut';
    public const CHART_PIE = 'pie';
    public const CHART_POLAR_AREA = 'polarArea';

    public const RANGE_LAST_24_HOURS = 'last_24_hours';
    public const RANGE_LAST_7_DAYS = 'last_7_days';
    public const RANGE_LAST_30_DAYS = 'last_30_days';
    public const RANGE_LAST_60_DAYS = 'last_60_days';
    public const RANGE_LAST_90_DAYS = 'last_90_days';

    /** @var array */
    private static $dateRanges = [
        self::RANGE_LAST_24_HOURS => 'Last 24 hours',
        self::RANGE_LAST_7_DAYS => 'Last 7 days',
        self::RANGE_LAST_30_DAYS => 'Last 30 days',
        self::RANGE_LAST_60_DAYS => 'Last 60 days',
        self::RANGE_LAST_90_DAYS => 'Last 90 days',
    ];

    public function getDateRanges(): array
    {
        $dateRanges = self::$dateRanges;

        array_walk(
            $dateRanges,
            static function (&$value) {
                $value = ExpressForms::t($value);
            }
        );

        return self::$dateRanges;
    }

    /**
     * @return array - [$dateRangeStart, $dateRangeEnd]
     */
    public function getRange(string $rangeType): array
    {
        if (!\array_key_exists($rangeType, self::$dateRanges)) {
            throw new ExpressFormsException(sprintf("Range type '%s' not supported", $rangeType));
        }

        $rangeEnd = new Carbon(null, 'UTC');

        switch ($rangeType) {
            case self::RANGE_LAST_24_HOURS:
                $rangeStart = new Carbon('24 hours ago', 'UTC');

                break;

            case self::RANGE_LAST_7_DAYS:
                $rangeStart = new Carbon('7 days ago', 'UTC');

                break;

            case self::RANGE_LAST_30_DAYS:
                $rangeStart = new Carbon('30 days ago', 'UTC');

                break;

            case self::RANGE_LAST_60_DAYS:
                $rangeStart = new Carbon('60 days ago', 'UTC');

                break;

            case self::RANGE_LAST_90_DAYS:
                $rangeStart = new Carbon('90 days ago', 'UTC');

                break;

            default:
                $rangeStart = new Carbon('-1 month', 'UTC');

                break;
        }

        if (self::RANGE_LAST_24_HOURS !== $rangeType) {
            $rangeStart->setTime(0, 0);
        }

        return [$rangeStart, $rangeEnd];
    }
}
