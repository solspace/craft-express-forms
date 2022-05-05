<?php

namespace Solspace\ExpressForms\serializers;

class IntegrationSerializer
{
    public function toJson(IntegrationMappingInterface $integrationMapping): string
    {
        return json_encode($integrationMapping);
    }

    public function toArray(IntegrationMappingInterface $integraionMapping): array
    {
        return $integraionMapping->jsonSerialize();
    }
}
