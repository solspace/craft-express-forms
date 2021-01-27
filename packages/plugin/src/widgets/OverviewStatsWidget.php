<?php

namespace Solspace\ExpressForms\widgets;

use Carbon\Carbon;
use craft\base\Widget;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\UrlHelper;
use Solspace\Commons\Dto\Charts\LinearChartData;
use Solspace\Commons\Dto\Charts\LinearItem;
use Solspace\Commons\Helpers\ColorHelper;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\resources\bundles\OverviewStatsWidgetBundle;
use Solspace\ExpressForms\services\Forms;
use Solspace\ExpressForms\services\Widgets;

class OverviewStatsWidget extends Widget
{
    /** @var string */
    public $title;

    /** @var array */
    public $formIds;

    /** @var bool */
    public $aggregate;

    /** @var string */
    public $dateRange;

    /** @var int */
    public $chartHeight;

    /** @var string */
    public $chartType;

    public static function displayName(): string
    {
        return ExpressForms::getInstance()->name.' '.ExpressForms::t('Overview Stats');
    }

    public static function iconPath(): string
    {
        return __DIR__.'/../icon-mask.svg';
    }

    public function getTitle(): string
    {
        return $this->title ?: static::displayName();
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if (null === $this->title) {
            $this->title = self::displayName();
        }

        if (null === $this->formIds) {
            $this->formIds = [];
        }

        if (null === $this->aggregate) {
            $this->aggregate = false;
        }

        if (null === $this->dateRange) {
            $this->dateRange = Widgets::RANGE_LAST_30_DAYS;
        }

        if (null === $this->chartHeight) {
            $this->chartHeight = 50;
        }

        if (null === $this->chartType) {
            $this->chartType = Widgets::CHART_LINE;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['formIds'], 'required'],
        ];
    }

    public function getBodyHtml(): string
    {
        if (!ExpressForms::getInstance()->isPro()) {
            return ExpressForms::t(
                "Requires <a href='{link}'>Pro</a> edition",
                ['link' => UrlHelper::cpUrl('express-forms/resources/explore')]
            );
        }

        \Craft::$app->view->registerAssetBundle(OverviewStatsWidgetBundle::class);
        $data = $this->getChartData();

        switch ($this->dateRange) {
            case Widgets::RANGE_LAST_7_DAYS:
                $incrementSkip = 1;

                break;

            case Widgets::RANGE_LAST_30_DAYS:
                $incrementSkip = 3;

                break;

            case Widgets::RANGE_LAST_60_DAYS:
                $incrementSkip = 6;

                break;

            case Widgets::RANGE_LAST_90_DAYS:
                $incrementSkip = 10;

                break;

            case Widgets::RANGE_LAST_24_HOURS:
            default:
                $incrementSkip = 1;

                break;
        }

        return \Craft::$app->view->renderTemplate(
            'express-forms/_widgets/overview-stats/body',
            [
                'chartData' => $data,
                'settings' => $this,
                'incrementSkip' => $incrementSkip,
            ]
        );
    }

    public function getSettingsHtml(): string
    {
        $forms = ExpressForms::getInstance()->forms->getAllForms();
        $formsOptions = [];
        foreach ($forms as $form) {
            $formsOptions[$form->getId()] = $form->getName();
        }

        return \Craft::$app->view->renderTemplate(
            'express-forms/_widgets/overview-stats/settings',
            [
                'settings' => $this,
                'formOptions' => $formsOptions,
                'dateRangeOptions' => ExpressForms::getInstance()->widgets->getDateRanges(),
                'chartTypes' => [
                    Widgets::CHART_LINE => 'Line',
                    Widgets::CHART_BAR => 'Bar',
                ],
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function getLinearSubmissionChartData(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        array $formIds,
        bool $aggregate = false
    ): LinearChartData {
        $submissions = Submission::TABLE;

        $diff = $rangeStart->diffInDays($rangeEnd);

        $labels = $dates = [];
        $dateContext = $rangeStart->copy();
        for ($i = 0; $i <= $diff; ++$i) {
            $labels[] = $dateContext->format('M j');
            $dates[] = $dateContext->format('Y-m-d');
            $dateContext->addDay();
        }

        $forms = $this->getFormsService()->getAllForms(true);
        $datasets = [];
        foreach ($formIds as $formId) {
            if (null !== $formId && !isset($forms[$formId])) {
                continue;
            }

            $query = (new Query())
                ->select(["DATE({$submissions}.[[dateCreated]]) as dt", "COUNT({$submissions}.[[id]]) as count"])
                ->from(Submission::TABLE)
                ->groupBy(['dt'])
            ;

            $query->where(
                [
                    'between',
                    "{$submissions}.[[dateCreated]]",
                    $rangeStart->toDateTimeString(),
                    $rangeEnd->toDateTimeString(),
                ]
            );

            $form = null;
            if ($aggregate) {
                $query->andWhere(['in', "{$submissions}.[[formId]]", $formIds]);
            } else {
                $form = $forms[$formId];
                $query->andWhere(["{$submissions}.[[formId]]" => $formId]);
            }

            if (version_compare(\Craft::$app->getVersion(), '3.1', '>=')) {
                $elements = Table::ELEMENTS;
                $query->innerJoin(
                    $elements,
                    "{$elements}.[[id]] = {$submissions}.[[id]] AND {$elements}.[[dateDeleted]] IS NULL"
                );
            }

            $result = $query->all();

            $data = [];
            foreach ($dates as $date) {
                $data[$date] = 0;
            }

            foreach ($result as $item) {
                $data[$item['dt']] = (int) $item['count'];
            }

            if ($form) {
                $color = ColorHelper::getRGBColor($form->getColor());
            } else {
                $color = [5, 148, 209];
            }

            $datasets[] = new LinearItem($form ? $form->getName() : 'Submissions', $color, $data);

            if ($aggregate) {
                break;
            }
        }

        return $this->getCompiledChartData($labels, $datasets);
    }

    private function getChartData(): LinearChartData
    {
        list($rangeStart, $rangeEnd) = $this->getWidgetsService()->getRange($this->dateRange);

        $formIds = $this->formIds;
        if ('*' === $formIds) {
            $formIds = array_keys($this->getFormsService()->getAllForms(true));
        }

        $chartData = $this->getLinearSubmissionChartData(
            $rangeStart,
            $rangeEnd,
            $formIds,
            (bool) $this->aggregate
        );

        $chartData->setChartType($this->chartType);

        return $chartData;
    }

    private function getWidgetsService(): Widgets
    {
        return ExpressForms::getInstance()->widgets;
    }

    private function getFormsService(): Forms
    {
        return ExpressForms::getInstance()->forms;
    }

    /**
     * @throws \Exception
     */
    private function getCompiledChartData(array $labels, array $datasets): LinearChartData
    {
        $chartData = new LinearChartData();
        $chartData->setLabels($labels);
        $chartData->setDatasets($datasets);

        return $chartData;
    }
}
