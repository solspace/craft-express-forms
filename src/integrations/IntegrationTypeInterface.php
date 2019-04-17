<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\objects\Integrations\Setting;

interface IntegrationTypeInterface extends \JsonSerializable
{
    const EVENT_AFTER_RESPONSE        = 'afterResponse';
    const EVENT_AFTER_SET_MAPPING     = 'afterSetMapping';
    const EVENT_FETCH_RESOURCES       = 'fetchResources';
    const EVENT_FETCH_RESOURCE_FIELDS = 'fetchResourceFields';

    const TYPE_CRM          = 'crm';
    const TYPE_MAILING_LIST = 'mailing-list';

    /**
     * Return an array of Setting objects to provide users with
     * input fields to fill out the settings
     *
     * @return Setting[]
     */
    public static function getSettingsManifest(): array;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getHandle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Do something before settings are rendered
     */
    public function beforeRenderUpdate();

    /**
     * Do something before settings are saved
     */
    public function beforeSaveSettings();

    /**
     * Do something after settings are saved
     */
    public function afterSaveSettings();

    /**
     * @throws ConnectionFailedException
     */
    public function checkConnection(): bool;

    /**
     * @return array
     */
    public function serializeSettings(): array;

    /**
     * @return bool
     */
    public function isMarkedForUpdate(): bool;

    /**
     * @return Resource[]
     * @throws ConnectionFailedException
     */
    public function fetchResources(): array;

    /**
     * @param string|int $resourceId
     *
     * @return ResourceField[]
     * @throws ConnectionFailedException
     */
    public function fetchResourceFields($resourceId): array;

    /**
     * @param IntegrationMappingInterface $mapping
     * @param array                       $postedData
     *
     * @return bool
     */
    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool;
}
