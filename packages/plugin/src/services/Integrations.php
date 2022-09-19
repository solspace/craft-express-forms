<?php

namespace Solspace\ExpressForms\services;

use craft\db\Query;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\CrmTypeInterface;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;
use Solspace\ExpressForms\providers\Plugin\ConfigProviderInterface;
use Solspace\ExpressForms\records\IntegrationResourceFieldRecord;
use Solspace\ExpressForms\records\IntegrationResourceRecord;

class Integrations extends BaseService
{
    const EVENT_BEFORE_BUILD_INTEGRATION = 'beforeBuildIntegration';
    const EVENT_AFTER_BUILD_INTEGRATION = 'afterBuildIntegration';
    const EVENT_REGISTER_INTEGRATIONS = 'registerIntegrations';

    const CONFIG_NAME = 'express-forms-integrations';

    /** @var IntegrationTypeInterface[] */
    private $integrationTypeCache;

    /**
     * @return IntegrationTypeInterface[]
     */
    public function getIntegrationTypes(): array
    {
        if (null === $this->integrationTypeCache) {
            $event = new RegisterIntegrationTypes($this->getConfig());
            $this->trigger(self::EVENT_REGISTER_INTEGRATIONS, $event);

            $this->integrationTypeCache = $event->getTypes();
        }

        return $this->integrationTypeCache;
    }

    /**
     * @return null|IntegrationTypeInterface
     */
    public function getIntegrationByClass(string $class)
    {
        foreach ($this->getIntegrationTypes() as $type) {
            if ($type instanceof $class) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param string $handle
     *
     * @return null|IntegrationTypeInterface
     */
    public function getIntegrationByHandle(string $handle = null)
    {
        foreach ($this->getIntegrationTypes() as $type) {
            if ($type->getHandle() === $handle) {
                return $type;
            }
        }

        return null;
    }

    public function getIntegrationMetadata(): array
    {
        $types = $this->getIntegrationTypes();

        $metadata = [];
        foreach ($types as $type) {
            if (!$type->isEnabled()) {
                continue;
            }

            $metadata[] = $this->getIntegrationTypeMetadata($type);
        }

        return $metadata;
    }

    public function getIntegrationTypeMetadata(IntegrationTypeInterface $type): array
    {
        $results = (new Query())
            ->select(['id', 'handle', 'name', 'settings'])
            ->from(IntegrationResourceRecord::TABLE)
            ->where(['typeClass' => \get_class($type)])
            ->orderBy(['sortOrder' => \SORT_ASC])
            ->all()
        ;

        $resources = [];
        foreach ($results as $result) {
            $fields = (new Query())
                ->select(['handle', 'name', 'type', 'required', 'settings', 'category'])
                ->from(IntegrationResourceFieldRecord::TABLE)
                ->where(['resourceId' => $result['id']])
                ->orderBy(['category' => \SORT_ASC, 'sortOrder' => \SORT_ASC])
                ->all()
            ;

            $fieldList = [];
            foreach ($fields as $field) {
                $fieldList[] = [
                    'handle' => $field['handle'],
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'required' => (bool) $field['required'],
                    'settings' => \GuzzleHttp\json_decode($field['settings'], true),
                    'category' => $field['category'],
                ];
            }

            $resources[] = [
                'handle' => $result['handle'],
                'name' => $result['name'],
                'settings' => \GuzzleHttp\json_decode($result['settings'], true),
                'fields' => $fieldList,
            ];
        }

        $integrationType = null;
        if ($type instanceof CrmTypeInterface) {
            $integrationType = IntegrationTypeInterface::TYPE_CRM;
        }

        if ($type instanceof MailingListTypeInterface) {
            $integrationType = IntegrationTypeInterface::TYPE_MAILING_LIST;
        }

        return [
            'name' => $type->getName(),
            'handle' => $type->getHandle(),
            'integrationType' => $integrationType,
            'resources' => $resources,
        ];
    }

    public function storeConfig(IntegrationTypeInterface $integrationType)
    {
        $integrationType->beforeSaveSettings();

        $config = $this->getConfig();
        $config[\get_class($integrationType)] = $integrationType->serializeSettings();

        /** @var ConfigProviderInterface $configProvider */
        $configProvider = ExpressForms::container()->get(ConfigProviderInterface::class);
        $configProvider->setConfig(self::CONFIG_NAME, $config);

        $integrationType->afterSaveSettings();
    }

    public function getResourceFields(IntegrationTypeInterface $type, string $resourceId): ResourceFieldCollection
    {
        $results = (new Query())
            ->select(
                ['[[rf.handle]]', '[[rf.name]]', '[[rf.type]]', '[[rf.required]]', '[[rf.settings]]', '[[rf.category]]']
            )
            ->from(IntegrationResourceFieldRecord::TABLE.' rf')
            ->innerJoin(IntegrationResourceRecord::TABLE.' r', '[[r.id]] = [[rf.resourceId]]')
            ->where(
                [
                    '[[r.typeClass]]' => \get_class($type),
                    '[[r.handle]]' => $resourceId,
                ]
            )
            ->orderBy(['[[rf.category]]' => \SORT_ASC, '[[rf.sortOrder]]' => \SORT_ASC])
            ->all()
        ;

        $fields = new ResourceFieldCollection();
        foreach ($results as $result) {
            $fields->addField(
                new ResourceField(
                    $result['name'],
                    $result['handle'],
                    $result['type'],
                    (bool) $result['required'],
                    \GuzzleHttp\json_decode($result['settings'] ?? '[]', true),
                    $result['category'] ?? null
                )
            );
        }

        return $fields;
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function fetchData(IntegrationTypeInterface $type)
    {
        $resources = $type->fetchResources();

        $resourceHandles = [];
        $resourceOrder = 1;
        foreach ($resources as $resource) {
            $handle = $resource->getHandle();

            $resourceHandles[] = $handle;

            $resourceRecord = IntegrationResourceRecord::findOne([
                'typeClass' => \get_class($type),
                'handle' => $handle,
            ]);

            if (!$resourceRecord) {
                $resourceRecord = new IntegrationResourceRecord();
                $resourceRecord->typeClass = \get_class($type);
                $resourceRecord->handle = $handle;
            }

            $resourceRecord->name = $resource->getName();
            $resourceRecord->settings = $resource->getSettings();
            $resourceRecord->sortOrder = $resourceOrder++;
            $resourceRecord->save();

            $fieldOrder = 1;
            if ($resourceRecord->id && !$resourceRecord->getErrors()) {
                $fields = $type->fetchResourceFields($handle);

                $fieldHandles = [];
                foreach ($fields as $field) {
                    $fieldHandles[] = $field->getHandle();

                    $fieldRecord = IntegrationResourceFieldRecord::findOne(
                        [
                            'resourceId' => $resourceRecord->id,
                            'handle' => $field->getHandle(),
                            'category' => $field->getCategory(),
                        ]
                    );

                    if (!$fieldRecord) {
                        $fieldRecord = new IntegrationResourceFieldRecord();
                        $fieldRecord->resourceId = $resourceRecord->id;
                        $fieldRecord->handle = $field->getHandle();
                    }

                    $fieldRecord->name = $field->getName();
                    $fieldRecord->type = $field->getType();
                    $fieldRecord->required = $field->isRequired();
                    $fieldRecord->settings = $field->getSettings();
                    $fieldRecord->category = $field->getCategory();
                    $fieldRecord->sortOrder = $fieldOrder++;
                    $fieldRecord->save();
                }

                $deletableFields = IntegrationResourceFieldRecord::find()
                    ->where(['not in', 'handle', $fieldHandles])
                    ->andWhere(['resourceId' => $resourceRecord->id])
                    ->all()
                ;

                foreach ($deletableFields as $field) {
                    $field->delete();
                }
            }
        }

        $deletableMailingLists = IntegrationResourceRecord::find()
            ->where(['not in', 'handle', $resourceHandles])
            ->andWhere(['typeClass' => \get_class($type)])
            ->all()
        ;

        foreach ($deletableMailingLists as $resource) {
            $resource->delete();
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getConfig(): array
    {
        /** @var ConfigProviderInterface $configProvider */
        $configProvider = ExpressForms::container()->get(ConfigProviderInterface::class);

        return $configProvider->getConfig(self::CONFIG_NAME);
    }
}
