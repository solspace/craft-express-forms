<?php

namespace Solspace\ExpressForms\decorators\Forms\Export;

use craft\helpers\StringHelper;
use Solspace\ExpressForms\decorators\AbstractDecorator;
use Solspace\ExpressForms\decorators\ExtraBundle;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\services\Export;

class XmlExporterDecorator extends AbstractDecorator implements ExtraBundle
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
        $event->addType('XML');
    }

    public function exportSubmissions(ExportSubmissionsEvent $event)
    {
        if ('xml' !== $event->getType()) {
            return;
        }

        $xml = new \SimpleXMLElement('<root/>');

        $submissions = $event->getSubmissions();
        foreach ($submissions as &$values) {
            $item = $xml->addChild('submission');

            foreach ($values as $key => &$value) {
                $handle = StringHelper::toCamelCase($key);

                if (\is_array($value)) {
                    $subItems = $item->addChild($handle);
                    foreach ($value as $subValue) {
                        $subItems->addChild('item', $subValue);
                    }
                } else {
                    if ($value instanceof \DateTime) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    if (\is_string($value)) {
                        $value = htmlentities($value);
                    }

                    $item->addChild($handle, $value);
                }
            }
        }

        $fileName = sprintf(
            '%s submissions %s.xml',
            $event->getForm()->getName(),
            date('Y-m-d H:i')
        );

        $response = $event->getResponse();
        $response->sendContentAsFile($xml->asXML(), $fileName, ['mimeType' => 'application/xml']);
    }
}
