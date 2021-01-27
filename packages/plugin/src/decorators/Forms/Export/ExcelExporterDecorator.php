<?php

namespace Solspace\ExpressForms\decorators\Forms\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\decorators\ExtraBundle;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\services\Export;

class ExcelExporterDecorator extends AbstractDecorator implements ExtraBundle
{
    public function getEventListenerList(): array
    {
        return [
            [Export::class, Export::EVENT_REGISTER_EXPORT_TYPES, [$this, 'registerType']],
            [Export::class, Export::EVENT_EXPORT_SUBMISSIONS, [$this, 'exportSubmissions']],
        ];
    }

    public function registerType(RegisterExportTypesEvent $event)
    {
        $event->addType('Excel');
    }

    public function exportSubmissions(ExportSubmissionsEvent $event)
    {
        if ('excel' !== $event->getType()) {
            return;
        }

        $submissions = $event->getSubmissions();
        if ($submissions) {
            $headings = [];
            foreach ($submissions[0] as $key => $value) {
                $headings[$key] = $key;
            }
            array_unshift($submissions, $headings);
        }

        foreach ($submissions as &$values) {
            foreach ($values as $index => &$value) {
                if (\is_array($value)) {
                    $value = implode(', ', $value);
                }

                if (\is_string($value)) {
                    $value = htmlentities($value);
                }
            }
            unset($value);
        }
        unset($values);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($submissions);

        $fileName = sprintf(
            '%s submissions %s.xlsx',
            $event->getForm()->getName(),
            date('Y-m-d H:i')
        );

        ob_start();

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $content = ob_get_contents();
        ob_end_clean();

        $response = $event->getResponse();
        $response->sendContentAsFile($content, $fileName, ['mimeType' => 'application/vnd.ms-excel']);
    }
}
