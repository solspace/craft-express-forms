<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

interface IntegrationMappingInterface extends \JsonSerializable
{
    public function getHandle(): string;

    public function getType(): IntegrationTypeInterface;

    public function getResourceId(): string;

    public function getResourceFields(): ResourceFieldCollection;

    /**
     * @return FieldInterface[]
     */
    public function getFieldMappings(): array;

    /**
     * @return null|FieldInterface
     */
    public function getField(string $mappingHandle);

    public function pushData(array $postedData): bool;
}
