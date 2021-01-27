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
     * @param null $payload
     */
    public function __construct(IntegrationTypeInterface $integrationType, string $queryAction, $payload = null)
    {
        $this->integrationType = $integrationType;
        $this->queryAction = $queryAction;
        $this->payload = $payload;

        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    public function getQueryAction(): string
    {
        return $this->queryAction;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $message): self
    {
        if (!\in_array($message, $this->errors, true)) {
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
     */
    public function setPayload($payload): self
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
     */
    public function setResponseData($responseData): self
    {
        $this->responseData = $responseData;

        return $this;
    }
}
