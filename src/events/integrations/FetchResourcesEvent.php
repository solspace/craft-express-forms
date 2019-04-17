<?php

namespace Solspace\ExpressForms\events\integrations;

use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use yii\base\Event;

class FetchResourcesEvent extends Event
{
    /** @var IntegrationTypeInterface */
    private $integrationType;

    /** @var Resource[] */
    private $resourceList;

    /**
     * FetchResourcesEvent constructor.
     *
     * @param IntegrationTypeInterface $integrationType
     * @param Resource[]               $resourceFieldList
     */
    public function __construct(IntegrationTypeInterface $integrationType, array $resourceFieldList)
    {
        $this->integrationType = $integrationType;
        $this->resourceList    = $resourceFieldList;

        parent::__construct();
    }

    /**
     * @return IntegrationTypeInterface
     */
    public function getIntegrationType(): IntegrationTypeInterface
    {
        return $this->integrationType;
    }

    /**
     * @return Resource[]
     */
    public function getResourceList(): array
    {
        return $this->resourceList ?? [];
    }

    /**
     * @param Resource[] $resourceList
     *
     * @return FetchResourcesEvent
     */
    public function setResourceList(array $resourceList = []): FetchResourcesEvent
    {
        $this->resourceList = $resourceList;

        return $this;
    }

    /**
     * @param Resource $resource
     *
     * @return FetchResourcesEvent
     */
    public function addResource(Resource $resource): FetchResourcesEvent
    {
        $this->resourceList[] = $resource;

        return $this;
    }
}
