<?php

namespace Solspace\ExpressForms\events\export;

use Solspace\ExpressForms\models\Form;
use yii\base\Event;
use yii\web\Response;

class ExportSubmissionsEvent extends Event
{
    /** @var string */
    private $type;

    /** @var Form */
    private $form;

    /** @var array */
    private $submissions;

    /** @var Response */
    private $response;

    /**
     * ExportSubmissionsEvent constructor.
     */
    public function __construct(string $type, Form $form, array $submissions, Response $response)
    {
        $this->type = $type;
        $this->form = $form;
        $this->submissions = $submissions;
        $this->response = $response;

        parent::__construct();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getSubmissions(): array
    {
        return $this->submissions;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
