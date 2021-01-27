<?php

namespace Solspace\ExpressForms\events\integrations;

use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use yii\base\Event;

class FetchResourcesEvent extends Event
{
    /** @var IntegrationTypeInterface */
    private $integrationType;

    /** @var resource[] */
    private $resourceList;

    /**
     * FetchResourcesEvent constructor.
     *
     * @param resource[] $resourceFieldList
     */
    public function __construct(IntegrationTypeInterface $integrationType, array $resourceFieldList)
    {
        $this->integrationType = $integrationType;
        $this->resourceList = $resourceFieldList;

        parent::__construct();
    }

    public function getIntegrationType(): IntegrationTypeInterface
    {
        return $this->integrationType;
    }

    /**
     * @return resource[]
     */
    public function getResourceList(): array
    {
        return $this->resourceList ?? [];
    }

    /**
     * @param resource[] $resourceList
     */
    public function setResourceList(array $resourceList = []): self
    {
        $this->resourceList = $resourceList;

        return $this;
    }

    public function addResource(Resource $resource): self
    {
        $this->resourceList[] = $resource;

        return $this;
    }
}
