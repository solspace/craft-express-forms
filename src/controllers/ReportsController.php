<?php

namespace Solspace\ExpressForms\controllers;

use Carbon\Carbon;
use craft\db\Query;
use craft\db\Table;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ChartHelper;
use craft\web\Controller;
use Solspace\Commons\Helpers\PermissionHelper;
use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\ExpressForms;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ReportsController extends Controller
{
    /**
     * Returns the data needed to display a Submissions chart.
     *
     * @return Response
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionSubmissionsIndex(): Response
    {
        PermissionHelper::requirePermission(ExpressForms::PERMISSION_SUBMISSIONS);

        // Required for Dashboard widget, unnecessary for Entries Index view
        $source = \Craft::$app->request->post('source');
        $formId = null;
        if ($source && strpos($source, 'form:') === 0) {
            $formId = (int) substr($source, 5);
        }

        $startDateParam = \Craft::$app->request->post('startDate');
        $endDateParam   = \Craft::$app->request->post('endDate');

        $startDate = new Carbon($startDateParam, 'UTC');
        $endDate   = new Carbon($endDateParam, 'UTC');
        $endDate->setTime(23, 59, 59);

        $intervalUnit = ChartHelper::getRunChartIntervalUnit($startDate, $endDate);
        $elements     = Table::ELEMENTS;
        $submissions  = Submission::TABLE_STD;

        // Prep the query
        $query = (new Query())
            ->select(['COUNT(*) as [[value]]'])
            ->from([Submission::TABLE . ' ' . $submissions])
            ->innerJoin(
                $elements,
                "$elements.[[id]] = $submissions.[[id]] AND $elements.[[dateDeleted]] IS NULL"
            );

        if ($formId) {
            $query->andWhere(['formId' => $formId]);
        }

        // Get the chart data table
        $dataTable = $this->getRunChartDataFromQuery(
            $query,
            $startDate,
            $endDate,
            Submission::TABLE_STD . '.dateCreated',
            [
                'intervalUnit' => $intervalUnit,
                'valueLabel'   => ExpressForms::t('Submissions'),
                'valueType'    => 'number',
            ]
        );

        // Get the total submissions
        $total = 0;

        foreach ($dataTable['rows'] as $row) {
            $total += $row[1];
        }

        $formats                 = ChartHelper::formats();
        $formats['numberFormat'] = ',.0f';

        return $this->asJson(
            [
                'dataTable' => $dataTable,
                'total'     => $total,
                'totalHtml' => $total,

                'formats'     => $formats,
                'orientation' => \Craft::$app->locale->getOrientation(),
                'scale'       => $intervalUnit,
            ]
        );
    }

    /**
     * @param Query  $query
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dateColumn
     * @param array  $options
     *
     * @return array
     * @throws Exception
     */
    private function getRunChartDataFromQuery(
        Query $query,
        Carbon $startDate,
        Carbon $endDate,
        string $dateColumn,
        array $options = []
    ): array {
        // Setup
        $options = array_merge(
            [
                'intervalUnit'  => null,
                'categoryLabel' => ExpressForms::t('Date'),
                'valueLabel'    => ExpressForms::t('Value'),
                'valueType'     => 'number',
            ],
            $options
        );

        $isMysql = \Craft::$app->getDb()->getIsMysql();

        if ($options['intervalUnit'] && \in_array($options['intervalUnit'], ['year', 'month', 'day', 'hour'], true)) {
            $intervalUnit = $options['intervalUnit'];
        } else {
            $intervalUnit = ChartHelper::getRunChartIntervalUnit($startDate, $endDate);
        }

        if ($isMysql) {
            $dateColumnSql = $dateColumn;
            $yearSql       = "YEAR({$dateColumnSql})";
            $monthSql      = "MONTH({$dateColumnSql})";
            $daySql        = "DAY({$dateColumnSql})";
            $hourSql       = "HOUR({$dateColumnSql})";
        } else {
            $dateColumnSql = "[[{$dateColumn}]]";
            $yearSql       = "EXTRACT(YEAR FROM {$dateColumnSql})";
            $monthSql      = "EXTRACT(MONTH FROM {$dateColumnSql})";
            $daySql        = "EXTRACT(DAY FROM {$dateColumnSql})";
            $hourSql       = "EXTRACT(HOUR FROM {$dateColumnSql})";
        }

        // Prepare the query
        switch ($intervalUnit) {
            case 'year':
                if ($isMysql) {
                    $sqlDateFormat = '%Y-01-01';
                } else {
                    $sqlDateFormat = 'YYYY-01-01';
                }
                $phpDateFormat = 'Y-01-01';
                $sqlGroup      = [$yearSql];
                break;
            case 'month':
                if ($isMysql) {
                    $sqlDateFormat = '%Y-%m-01';
                } else {
                    $sqlDateFormat = 'YYYY-MM-01';
                }
                $phpDateFormat = 'Y-m-01';
                $sqlGroup      = [$yearSql, $monthSql];
                break;
            case 'day':
                if ($isMysql) {
                    $sqlDateFormat = '%Y-%m-%d';
                } else {
                    $sqlDateFormat = 'YYYY-MM-DD';
                }
                $phpDateFormat = 'Y-m-d';
                $sqlGroup      = [$yearSql, $monthSql, $daySql];
                break;
            case 'hour':
                if ($isMysql) {
                    $sqlDateFormat = '%Y-%m-%d %H:00:00';
                } else {
                    $sqlDateFormat = 'YYYY-MM-DD HH24:00:00';
                }
                $phpDateFormat = 'Y-m-d H:00:00';
                $sqlGroup      = [$yearSql, $monthSql, $daySql, $hourSql];
                break;
            default:
                throw new Exception('Invalid interval unit: ' . $intervalUnit);
        }

        if ($isMysql) {
            $select = "DATE_FORMAT({$dateColumnSql}, '{$sqlDateFormat}') AS [[date]]";
        } else {
            $select = "to_char({$dateColumnSql}, '{$sqlDateFormat}') AS [[date]]";
        }

        $sqlGroup[] = '[[date]]';

        // Prepare the query
        $condition = ['and', "{$dateColumnSql} >= :startDate", "{$dateColumnSql} < :endDate"];
        $params    = [
            ':startDate' => $startDate->toDateTimeString(),
            ':endDate'   => $endDate->toDateTimeString(),
        ];
        $orderBy   = ['date' => SORT_ASC];

        // If this is an element query, modify the prepared query directly
        if ($query instanceof ElementQueryInterface) {
            $query = $query->prepare(\Craft::$app->getDb()->getQueryBuilder());
            /** @var Query $subQuery */
            $subQuery = $query->from['subquery'];
            $subQuery
                ->addSelect($query->select)
                ->addSelect([$select])
                ->andWhere($condition, $params)
                ->groupBy($sqlGroup)
                ->orderBy($orderBy);
            $query
                ->select(['subquery.value', 'subquery.date'])
                ->orderBy($orderBy);
        } else {
            $query
                ->addSelect([$select])
                ->andWhere($condition, $params)
                ->groupBy($sqlGroup)
                ->orderBy($orderBy);
        }

        // Execute the query
        $results = $query->all();

        // Assemble the data
        $rows = [];

        $cursorDate   = $startDate;
        $endTimestamp = $endDate->getTimestamp();

        while ($cursorDate->getTimestamp() < $endTimestamp) {
            // Do we have a record for this date?
            $formattedCursorDate = $cursorDate->format($phpDateFormat);

            if (isset($results[0]) && $results[0]['date'] === $formattedCursorDate) {
                $value = (float) $results[0]['value'];
                array_shift($results);
            } else {
                $value = 0;
            }

            $rows[] = [$formattedCursorDate, $value];
            $cursorDate->modify('+1 ' . $intervalUnit);
        }

        return [
            'columns' => [
                [
                    'type'  => $intervalUnit === 'hour' ? 'datetime' : 'date',
                    'label' => $options['categoryLabel'],
                ],
                [
                    'type'  => $options['valueType'],
                    'label' => $options['valueLabel'],
                ],
            ],
            'rows'    => $rows,
        ];
    }
}
