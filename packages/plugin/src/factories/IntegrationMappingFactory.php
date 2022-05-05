<?php

namespace Solspace\ExpressForms\factories;

use Solspace\ExpressForms\events\integrations\MappingAfterBuildFromArrayEvent;
use Solspace\ExpressForms\events\integrations\MappingBuildFromArrayEvent;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\IntegrationMapping;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\IntegrationMappingCollection;
use yii\base\Event;

class IntegrationMappingFactory
{
    public const EVENT_BEFORE_BUILD_FROM_ARRAY = 'beforeBuildFromArray';
    public const EVENT_AFTER_BUILD_FROM_ARRAY = 'afterBuildFromArray';

    public function fromArray(Form $form, array $data): ?IntegrationMappingCollection
    {
        $event = new MappingBuildFromArrayEvent($form, $data);
        Event::trigger($this, self::EVENT_BEFORE_BUILD_FROM_ARRAY, $event);

        if (!$event->isValid) {
            return null;
        }

        $mappingCollection = new IntegrationMappingCollection();
        foreach ($data as $handle => $mappingData) {
            if ($mappingData instanceof IntegrationMappingInterface) {
                $mappingCollection->addMapping($mappingData);

                continue;
            }

            if (!isset($mappingData['resourceId'], $mappingData['fieldMap'])) {
                continue;
            }

            $resourceId = $mappingData['resourceId'];

            $type = ExpressForms::getInstance()->integrations->getIntegrationByHandle($handle);
            if ($type) {
                $fields = ExpressForms::getInstance()->integrations->getResourceFields($type, $resourceId);
                $mappingCollection->addMapping(
                    new IntegrationMapping($form, $type, $resourceId, $fields, $mappingData['fieldMap'])
                );
            }
        }

        $event = new MappingAfterBuildFromArrayEvent($form, $mappingCollection);
        Event::trigger($this, self::EVENT_AFTER_BUILD_FROM_ARRAY, $event);

        if (!$event->isValid) {
            return null;
        }

        return $mappingCollection;
    }
}
