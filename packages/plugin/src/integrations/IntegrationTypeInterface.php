<?php

namespace Solspace\ExpressForms\integrations;

use Solspace\ExpressForms\objects\Integrations\Setting;

interface IntegrationTypeInterface extends \JsonSerializable
{
    public const EVENT_AFTER_RESPONSE = 'afterResponse';
    public const EVENT_AFTER_SET_MAPPING = 'afterSetMapping';
    public const EVENT_FETCH_RESOURCES = 'fetchResources';
    public const EVENT_FETCH_RESOURCE_FIELDS = 'fetchResourceFields';

    public const TYPE_CRM = 'crm';
    public const TYPE_MAILING_LIST = 'mailing-list';

    /**
     * Return an array of Setting objects to provide users with
     * input fields to fill out the settings.
     *
     * @return Setting[]
     */
    public static function getSettingsManifest(): array;

    public function getName(): string;

    public function getHandle(): string;

    public function getDescription(): string;

    public function isEnabled(): bool;

    /**
     * Do something before settings are rendered.
     */
    public function beforeRenderUpdate(): void;

    /**
     * Do something before settings are saved.
     */
    public function beforeSaveSettings(): void;

    /**
     * Do something after settings are saved.
     */
    public function afterSaveSettings(): void;

    public function checkConnection(): bool;

    public function serializeSettings(): array;

    public function isMarkedForUpdate(): bool;

    public function fetchResources(): array;

    public function fetchResourceFields(int|string $resourceId): array;

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool;
}
