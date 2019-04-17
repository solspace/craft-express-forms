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
     *
     * @param string   $type
     * @param Form     $form
     * @param array    $submissions
     * @param Response $response
     */
    public function __construct(string $type, Form $form, array $submissions, Response $response)
    {
        $this->type        = $type;
        $this->form        = $form;
        $this->submissions = $submissions;
        $this->response    = $response;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getSubmissions(): array
    {
        return $this->submissions;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
