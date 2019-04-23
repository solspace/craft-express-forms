<?php

namespace Solspace\ExpressForms\integrations\types;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Solspace\Commons\Helpers\StringHelper;
use Solspace\ExpressForms\events\integrations\FetchResourceFieldsEvent;
use Solspace\ExpressForms\events\integrations\FetchResourcesEvent;
use Solspace\ExpressForms\events\integrations\IntegrationValueMappingEvent;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\exceptions\Integrations\IntegrationException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\AbstractIntegrationType;
use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Solspace\ExpressForms\objects\Integrations\Setting;
use yii\base\Event;

class MailChimp extends AbstractIntegrationType implements MailingListTypeInterface
{
    const FIELD_TARGET_EMAIL  = 'mailchimpTargetEmail';
    const FIELD_DOUBLE_OPT_IN = 'mailchimpOptIn';

    /** @var string */
    protected $apiKey;

    /** @var bool */
    protected $doubleOptIn;

    /** @var string */
    protected $dataCenter;

    /**
     * @return array
     */
    public static function getSettingsManifest(): array
    {
        return [
            new Setting('API Key', 'apiKey'),
            new Setting('Double Opt-In', 'doubleOptIn', 'Toggle on if you\'d like your users to receive a confirmation email to confirm subscription.', Setting::TYPE_BOOLEAN),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'MailChimp';
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return 'mail-chimp';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Send and map submission data to MailChimp to subscribe users to a mailing list.';
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
            $response = $client->get($this->getEndpoint('/'));
            $json     = \GuzzleHttp\json_decode((string) $response->getBody(), false);

            if (isset($json->error) && !empty($json->error)) {
                throw new ConnectionFailedException($json->error);
            }

            return isset($json->account_id) && !empty($json->account_id);
        } catch (RequestException $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    public function beforeSaveSettings()
    {
        $this->dataCenter = null;
        if (preg_match('/-([a-zA-Z]+[\d]+)$/', $this->getApiKey(), $matches)) {
            $this->dataCenter = $matches[1];
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
     * @return MailChimp
     */
    public function setApiKey(string $apiKey): MailChimp
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDoubleOptIn(): bool
    {
        return (bool) $this->doubleOptIn;
    }

    /**
     * @param bool $doubleOptIn
     *
     * @return MailChimp
     */
    public function setDoubleOptIn(bool $doubleOptIn): MailChimp
    {
        $this->doubleOptIn = $doubleOptIn;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataCenter()
    {
        return $this->dataCenter;
    }

    /**
     * @param string $dataCenter
     *
     * @return MailChimp
     */
    public function setDataCenter(string $dataCenter = null): MailChimp
    {
        $this->dataCenter = $dataCenter;

        return $this;
    }

    /**
     * @return array
     */
    public function serializeSettings(): array
    {
        return [
            'apiKey'      => $this->getApiKey(),
            'doubleOptIn' => $this->isDoubleOptIn(),
            'dataCenter'  => $this->dataCenter,
        ];
    }

    /**
     * @return Resource[]
     */
    public function fetchResources(): array
    {
        $client   = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/lists');

        try {
            $response = $client->get(
                $endpoint,
                [
                    'query' => [
                        'fields' => 'lists.id,lists.name,lists.stats.member_count',
                        'count'  => 999,
                    ],
                ]
            );
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new IntegrationException(
                ExpressForms::t(
                    'Could not fetch {serviceProvider} lists',
                    ['serviceProvider' => $this->getName()]
                )
            );
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        $lists = [];
        if (isset($json->lists)) {
            foreach ($json->lists as $list) {
                if (isset($list->id, $list->name)) {
                    $lists[] = new Resource($this, $list->name, $list->id);
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
        $endpoint = $this->getEndpoint("/lists/$resourceId/merge-fields");

        try {
            $response = $client->get($endpoint, ['query' => ['count' => 999]]);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        $fieldList = [
            new ResourceField('Opt-in Field', 'mailchimpOptIn', 'bool', true),
            new ResourceField('Email', 'mailchimpTargetEmail', 'string', true),
        ];

        if (isset($json->merge_fields)) {
            foreach ($json->merge_fields as $field) {
                $type = $field->type;
                if (null === $type) {
                    continue;
                }

                $fieldList[] = new ResourceField($field->name, $field->tag, $type, $field->required);
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
        $endpoint = $this->getEndpoint("lists/{$mapping->getResourceId()}");

        $optIn     = $mapping->getField(self::FIELD_DOUBLE_OPT_IN);
        $recipient = $mapping->getField(self::FIELD_TARGET_EMAIL);

        if (!$recipient || ($optIn && (bool) $optIn->getValue() === false)) {
            return false;
        }

        $emails = StringHelper::extractSeparatedValues($recipient->getValueAsString());

        $mappedValues = [];
        $mappedFields = $mapping->getFieldMappings();
        foreach ($mappedFields as $key => $field) {
            if (in_array($key, [self::FIELD_DOUBLE_OPT_IN, self::FIELD_TARGET_EMAIL], true)) {
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
            $members = [];
            foreach ($emails as $email) {
                $memberData = [
                    'email_address' => $email,
                    'status'        => $this->isDoubleOptIn() ? 'pending' : 'subscribed',
                ];

                if (!empty($mappedValues)) {
                    $memberData['merge_fields'] = $mappedValues;
                }

                $members[] = $memberData;
            }

            $data = ['members' => $members, 'update_existing' => true];

            $response = $client->post($endpoint, ['json' => $data]);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            return false;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $this->getLogger()->error('Could not add emails to lists', ['response' => (string) $response->getBody()]);

            return false;
        }

        $jsonResponse = \GuzzleHttp\json_decode((string) $response->getBody(), false);
        if (isset($jsonResponse->error_count) && $jsonResponse->error_count > 0) {
            $this->getLogger()->error(json_encode($jsonResponse->errors), ['response' => $jsonResponse]);

            return false;
        }

        return $statusCode === 200;
    }

    /**
     * @return string
     */
    protected function getApiRootUrl(): string
    {
        return "https://{$this->dataCenter}.api.mailchimp.com/3.0/";
    }

    /**
     * @return Client
     */
    private function generateAuthorizedClient(): Client
    {
        return new Client(['auth' => ['mailchimp', $this->getApiKey()]]);
    }
}
