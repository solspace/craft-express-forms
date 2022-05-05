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

    public function registerType(RegisterExportTypesEvent $event): void
    {
        $event->addType('CSV');
    }

    public function exportSubmissions(ExportSubmissionsEvent $event): void
    {
        if ('csv' !== $event->getType()) {
            return;
        }

        $submissions = $event->getSubmissions();

        ob_start();
        $fp = fopen('php://output', 'w');
        if (!empty($submissions)) {
            fputcsv($fp, array_keys($submissions[0]));
            foreach ($submissions as &$values) {
                foreach ($values as $index => &$value) {
                    if (\is_array($value)) {
                        $value = implode(', ', $value);
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    } elseif (\is_bool($value)) {
                        $value = $value ? 'yes' : 'no';
                    }

                    $value = htmlentities($value ?? '');
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
