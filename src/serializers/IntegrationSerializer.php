<?php

namespace Solspace\ExpressForms\serializers;

class IntegrationSerializer
{
    /**
     * @param IntegrationMappingInterface $integrationMapping
     *
     * @return string
     */
    public function toJson(IntegrationMappingInterface $integrationMapping): string
    {
        return \GuzzleHttp\json_encode($integrationMapping);
    }

    /**
     * @param IntegrationMappingInterface $integraionMapping
     *
     * @return array|null
     */
    public function toArray(IntegrationMappingInterface $integraionMapping)
    {
        if (null === $integraionMapping) {
            return null;
        }

        return $integraionMapping->jsonSerialize();
    }
}
