<?php

namespace Solspace\ExpressForms\serializers;

class IntegrationSerializer
{
    public function toJson(IntegrationMappingInterface $integrationMapping): string
    {
        return \GuzzleHttp\json_encode($integrationMapping);
    }

    /**
     * @return null|array
     */
    public function toArray(IntegrationMappingInterface $integraionMapping)
    {
        if (null === $integraionMapping) {
            return null;
        }

        return $integraionMapping->jsonSerialize();
    }
}
