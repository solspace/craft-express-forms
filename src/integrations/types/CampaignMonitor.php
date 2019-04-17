<?php

namespace Solspace\ExpressForms\integrations\types;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\events\integrations\FetchResourceFieldsEvent;
use Solspace\ExpressForms\events\integrations\FetchResourcesEvent;
use Solspace\ExpressForms\events\integrations\IntegrationValueMappingEvent;
use Solspace\ExpressForms\events\integrations\PushResponseEvent;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\AbstractIntegrationType;
use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Solspace\ExpressForms\objects\Integrations\Setting;
use yii\base\Event;

class CampaignMonitor extends AbstractIntegrationType implements MailingListTypeInterface
{
    const FIELD_TARGET_EMAIL = 'campaignMonitorTargetEmail';
    const FIELD_OPT_IN       = 'campaignMonitorOptIn';

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $clientId;

    /**
     * @return array
     */
    public static function getSettingsManifest(): array
    {
        return [
            new Setting('API Key', 'apiKey', Setting::TYPE_TEXT),
            new Setting('Client ID', 'clientId', Setting::TYPE_TEXT),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Campaign Monitor';
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return 'campaign-monitor';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Send and map submission data to Campaign Monitor to subscribe users to a mailing list.';
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !empty($this->getApiKey());
    }

    /**
     * @return bool
     * @throws ConnectionFailedException
     */
    public function checkConnection(): bool
    {
        $client = $this->generateAuthorizedClient();

        try {
            $response = $client->get($this->getEndpoint('/clients/' . $this->getClientId() . '.json'));
            $json     = \GuzzleHttp\json_decode((string) $response->getBody(), false);

            return isset($json->ApiKey) && !empty($json->ApiKey);
        } catch (RequestException $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return CampaignMonitor
     */
    public function setApiKey(string $apiKey = null): CampaignMonitor
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     *
     * @return CampaignMonitor
     */
    public function setClientId(string $clientId = null): CampaignMonitor
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return array
     */
    public function serializeSettings(): array
    {
        return [
            'apiKey'   => $this->getApiKey(),
            'clientId' => $this->getClientId(),
        ];
    }

    /**
     * @return Resource[]
     */
    public function fetchResources(): array
    {
        $client   = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/clients/' . $this->getClientId() . '/lists.json');

        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new ConnectionFailedException(
                ExpressForms::t('Could not connect to API endpoint')
            );
        }

        $status = $response->getStatusCode();
        if ($status !== 200) {
            throw new ConnectionFailedException(
                ExpressForms::t('Could not fetch mailing lists')
            );
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        $lists = [];
        if (is_array($json)) {
            foreach ($json as $list) {
                if (isset($list->ListID, $list->Name)) {
                    $lists[] = new Resource(
                        $this,
                        $list->Name,
                        $list->ListID,
                        (array) $list
                    );
                }
            }
        }

        $event = new FetchResourcesEvent($this, $lists);
        Event::trigger($this, self::EVENT_FETCH_RESOURCES, $event);

        return $event->getResourceList();
    }

    /**
     * @param int|string $resourceId
     *
     * @return ResourceField[]
     */
    public function fetchResourceFields($resourceId): array
    {
        $client   = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint("/lists/$resourceId/customfields.json");

        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new ConnectionFailedException(
                ExpressForms::t('Could not connect to API endpoint')
            );
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        $fieldList = [
            new ResourceField('Opt-in Field', self::FIELD_OPT_IN, 'bool', true),
            new ResourceField('Email', self::FIELD_TARGET_EMAIL, 'Text', true),
            new ResourceField('Name', 'Name', 'Text', false),
        ];

        if (is_array($json)) {
            foreach ($json as $field) {
                $fieldList[] = new ResourceField(
                    $field->FieldName,
                    str_replace(['[', ']'], '', $field->Key),
                    $field->DataType,
                    false,
                    (array) $field
                );
            }
        }

        $event = new FetchResourceFieldsEvent($this, $resourceId, $fieldList);
        Event::trigger($this, self::EVENT_FETCH_RESOURCE_FIELDS, $event);

        return $event->getResourceFieldsList();
    }

    /**
     * @param IntegrationMappingInterface $mapping
     * @param array                       $postedData
     *
     * @return bool
     */
    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        $client   = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint("/subscribers/{$mapping->getResourceId()}.json");

        $optIn     = $mapping->getField(self::FIELD_OPT_IN);
        $recipient = $mapping->getField(self::FIELD_TARGET_EMAIL);

        if (!$recipient || ($optIn && (bool) $optIn->getValue() === false)) {
            return false;
        }

        $emails = StringHelper::extractSeparatedValues($recipient->getValueAsString());

        $mappedValues = [];
        $mappedFields = $mapping->getFieldMappings();
        foreach ($mappedFields as $key => $field) {
            if (in_array($key, [self::FIELD_OPT_IN, self::FIELD_TARGET_EMAIL], true)) {
                continue;
            }

            $resourceField = $mapping->getResourceFields()->get($key);
            if (null === $resourceField) {
                continue;
            }

            $mappedValues[$key] = $field->getValueAsString();
        }


        $event = new IntegrationValueMappingEvent($mappedValues);
        Event::trigger($this, self::EVENT_AFTER_SET_MAPPING, $event);

        $mappedValues = $event->getMappedValues();

        try {
            $customFields = [];
            foreach ($mappedValues as $key => $value) {
                if ($key === 'Name') {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $customFields[] = [
                            'Key'   => $key,
                            'Value' => $subValue,
                        ];
                    }
                } else {
                    $customFields[] = [
                        'Key'   => $key,
                        'Value' => $value,
                    ];
                }
            }

            foreach ($emails as $email) {
                $data = [
                    'EmailAddress'                           => $email,
                    'Name'                                   => $mappedValues['Name'] ?? '',
                    'CustomFields'                           => $customFields,
                    'Resubscribe'                            => true,
                    'RestartSubscriptionBasedAutoresponders' => true,
                    'ConsentToTrack'                         => 'Yes',
                ];

                $response = $client->post($endpoint, ['json' => $data]);

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            }
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new ConnectionFailedException(
                ExpressForms::t('Could not connect to API endpoint')
            );
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getApiRootUrl(): string
    {
        return 'https://api.createsend.com/api/v3.2/';
    }

    /**
     * @return Client
     */
    private function generateAuthorizedClient(): Client
    {
        return new Client(['auth' => [$this->getApiKey(), 'express-forms']]);
    }
}
