<?php

namespace Solspace\ExpressForms\services;

use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\models\Form;
use yii\web\Response;

class Export extends BaseService
{
    public const EVENT_REGISTER_EXPORT_TYPES = 'registerExportTypes';
    public const EVENT_EXPORT_SUBMISSIONS = 'exportSubmissions';

    private ?array $types = null;

    public function getExportTypes(): array
    {
        if (null === $this->types) {
            $event = new RegisterExportTypesEvent();
            $this->trigger(self::EVENT_REGISTER_EXPORT_TYPES, $event);

            $this->types = $event->getTypes();
        }

        return $this->types;
    }

    public function exportSubmissions(string $type, Form $form, array $submissions, Response $response): Response
    {
        $this->trigger(
            self::EVENT_EXPORT_SUBMISSIONS,
            new ExportSubmissionsEvent(
                $type,
                $form,
                $submissions,
                $response
            )
        );

        return $response;
    }
}
