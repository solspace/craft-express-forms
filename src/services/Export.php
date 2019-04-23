<?php

namespace Solspace\ExpressForms\services;

use Solspace\ExpressForms\elements\Submission;
use Solspace\ExpressForms\events\export\ExportSubmissionsEvent;
use Solspace\ExpressForms\events\export\RegisterExportTypesEvent;
use Solspace\ExpressForms\models\Form;
use yii\web\Response;

class Export extends BaseService
{
    const EVENT_REGISTER_EXPORT_TYPES = 'registerExportTypes';
    const EVENT_EXPORT_SUBMISSIONS    = 'exportSubmissions';

    /** @var array */
    private $types;

    /**
     * @return array
     */
    public function getExportTypes(): array
    {
        if (null === $this->types) {
            $event = new RegisterExportTypesEvent();
            $this->trigger(self::EVENT_REGISTER_EXPORT_TYPES, $event);

            $this->types = $event->getTypes();
        }

        return $this->types;
    }

    /**
     * @param string       $type
     * @param Form         $form
     * @param Submission[] $submissions
     * @param Response     $response
     *
     * @return Response
     */
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
