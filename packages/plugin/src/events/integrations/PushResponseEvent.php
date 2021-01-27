<?php

namespace Solspace\ExpressForms\events\integrations;

use Psr\Http\Message\ResponseInterface;
use yii\base\Event;

class PushResponseEvent extends Event
{
    /** @var ResponseInterface */
    private $response;

    /**
     * PushResponseEvent constructor.
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct();
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
