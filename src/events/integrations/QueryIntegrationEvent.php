<?php

namespace Solspace\ExpressForms\events\integrations;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;

class QueryIntegrationEvent extends CancelableEvent
{
    /** @var IntegrationTypeInterface */
    private $integrationType;

    /** @var string */
    private $queryAction;

    /** @var mixed */
    private $payload;

    /** @var mixed */
    private $responseData;

    /** @var array */
    private $errors = [];

    /**
     * QueryIntegrationEvent constructor.
     *
     * @param IntegrationTypeInterface $integrationType
     * @param string                   $queryAction
     * @param null                     $payload
     */
    public function __construct(IntegrationTypeInterface $integrationType, string $queryAction, $payload = null)
    {
        $this->integrationType = $integrationType;
        $this->queryAction     = $queryAction;
        $this->payload         = $payload;

        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    /**
     * @return string
     */
    public function getQueryAction(): string
    {
        return $this->queryAction;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $message
     *
     * @return QueryIntegrationEvent
     */
    public function addError(string $message): QueryIntegrationEvent
    {
        if (!in_array($message, $this->errors, true)) {
            $this->errors[] = $message;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     *
     * @return QueryIntegrationEvent
     */
    public function setPayload($payload): QueryIntegrationEvent
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param mixed $responseData
     *
     * @return QueryIntegrationEvent
     */
    public function setResponseData($responseData): QueryIntegrationEvent
    {
        $this->responseData = $responseData;

        return $this;
    }
}
