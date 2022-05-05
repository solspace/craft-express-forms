<?php

namespace Solspace\ExpressForms\decorators\Forms\Export;

use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\decorators\ExtraBundle;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\services\Export;

class JsonExporterDecorator extends AbstractDecorator implements ExtraBundle
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
        $event->addType('JSON');
    }

    public function exportSubmissions(ExportSubmissionsEvent $event): void
    {
        if ('json' !== $event->getType()) {
            return;
        }

        $submissions = $event->getSubmissions();
        $content = \GuzzleHttp\json_encode($submissions, \JSON_PRETTY_PRINT);

        $fileName = sprintf(
            '%s submissions %s.json',
            $event->getForm()->getName(),
            date('Y-m-d H:i')
        );

        $response = $event->getResponse();
        $response->sendContentAsFile($content, $fileName, ['mimeType' => 'application/json']);
    }
}
