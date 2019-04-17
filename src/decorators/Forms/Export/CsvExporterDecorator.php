<?php

namespace Solspace\ExpressForms\decorators\Forms\Export;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\services\Export;

class CsvExporterDecorator extends AbstractDecorator
{
    public function getEventListenerList(): array
    {
        return [
            [Export::class, Export::EVENT_REGISTER_EXPORT_TYPES, [$this, 'registerType']],
            [Export::class, Export::EVENT_EXPORT_SUBMISSIONS, [$this, 'exportSubmissions']],
        ];
    }

    /**
     * @param RegisterExportTypesEvent $event
     */
    public function registerType(RegisterExportTypesEvent $event)
    {
        $event->addType('CSV');
    }

    /**
     * @param ExportSubmissionsEvent $event
     */
    public function exportSubmissions(ExportSubmissionsEvent $event)
    {
        if ($event->getType() !== 'csv') {
            return;
        }

        $submissions = $event->getSubmissions();

        ob_start();
        $fp = fopen('php://output', 'wb');
        if (!empty($submissions)) {
            fputcsv($fp, array_keys($submissions[0]));
            foreach ($submissions AS &$values) {
                foreach ($values as $index => &$value) {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    } else if ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    } else if (is_bool($value)) {
                        $value = $value ? 'yes' : 'no';
                    }

                    $value = htmlentities($value);
                }
                unset($value);

                fputcsv($fp, $values);
            }
            unset($values);
            fclose($fp);
        }
        $content = ob_get_contents();
        ob_end_clean();

        $fileName = sprintf(
            '%s submissions %s.csv',
            $event->getForm()->getName(),
            date('Y-m-d H:i')
        );

        $response = $event->getResponse();
        $response->sendContentAsFile($content, $fileName, ['mimeType' => 'text/csv']);
    }
}
