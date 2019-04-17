<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

interface IntegrationMappingInterface extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getHandle(): string;

    /**
     * @return IntegrationTypeInterface
     */
    public function getType(): IntegrationTypeInterface;

    /**
     * @return string
     */
    public function getResourceId(): string;

    /**
     * @return ResourceFieldCollection
     */
    public function getResourceFields(): ResourceFieldCollection;

    /**
     * @return FieldInterface[]
     */
    public function getFieldMappings(): array;

    /**
     * @param string $mappingHandle
     *
     * @return FieldInterface|null
     */
    public function getField(string $mappingHandle);

    /**
     * @param array $postedData
     *
     * @return bool
     */
    public function pushData(array $postedData): bool;
}
