<?php

namespace Solspace\ExpressForms\integrations\types;

use Carbon\Carbon;
use craft\helpers\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Solspace\ExpressForms\events\integrations\FetchResourceFieldsEvent;
use Solspace\ExpressForms\events\integrations\FetchResourcesEvent;
use Solspace\ExpressForms\events\integrations\IntegrationValueMappingEvent;
use Solspace\ExpressForms\events\integrations\PushResponseEvent;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\exceptions\Integrations\IntegrationException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\integrations\AbstractIntegrationType;
use Solspace\ExpressForms\integrations\CrmTypeInterface;
use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\objects\Integrations\Setting;
use yii\base\Event;

class Salesforce extends AbstractIntegrationType implements CrmTypeInterface
{
    public const RESOURCE_LEAD = 'Lead';
    public const RESOURCE_OPPORTUNITY = 'Opportunity';

    public const FIELD_CATEGORY_OPPORTUNITY = 'Opportunity';
    public const FIELD_CATEGORY_ACCOUNT = 'Account';
    public const FIELD_CATEGORY_CONTACT = 'Contact';

    protected ?string $consumerKey = null;
    protected ?string $consumerSecret = null;
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected bool $assignOwner = false;
    protected bool $sandboxMode = false;
    protected bool $customUrl = false;
    protected ?string $instance = null;
    protected ?string $closeDate = null;
    protected ?string $stageName = null;

    public static function getSettingsManifest(): array
    {
        return [
            new Setting('Consumer Key', 'consumerKey'),
            new Setting('Consumer Secret', 'consumerSecret'),
            new Setting(
                'Assign Owner?',
                'assignOwner',
                'Enabling this will make Salesforce assign a lead owner based on lead owner assignment rules.',
                Setting::TYPE_BOOLEAN
            ),
            new Setting(
                'Sandbox mode?',
                'sandboxMode',
                'Enable this if your Salesforce account is in Sandbox mode (connects to "test.salesforce.com" instead of "login.salesforce.com").',
                Setting::TYPE_BOOLEAN
            ),
            new Setting(
                'Using custom URL?',
                'customUrl',
                'Enable this if you connect to your Salesforce account with a custom company URL (e.g. \'mycompany.my.salesforce.com\').',
                Setting::TYPE_BOOLEAN
            ),
            new Setting(
                'Close Date (required for Opportunity only)',
                'closeDate',
                'Enter a relative textual date string for the Close Date of the newly created Opportunity (e.g. \'7 days\').'
            ),
            new Setting(
                'Stage Name (required for Opportunity only)',
                'stageName',
                'Enter the Stage Name the newly created Opportunity should be assigned to (e.g. \'Prospecting\').'
            ),
        ];
    }

    public function getName(): string
    {
        return 'Salesforce';
    }

    public function getHandle(): string
    {
        return 'salesforce';
    }

    public function getDescription(): string
    {
        return 'Send and map submission data to your choice of Salesforce Lead or Opportunity, Account and Contact resources.';
    }

    public function isEnabled(): bool
    {
        return !empty($this->getAccessToken());
    }

    public function checkConnection(): bool
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/');

        try {
            $response = $client->get($endpoint);
            $json = json_decode((string) $response->getBody(), true);

            return !empty($json);
        } catch (RequestException $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function beforeRenderUpdate(): void
    {
        if (isset($_GET['code'])) {
            $payload = [
                'grant_type' => 'authorization_code',
                'client_id' => $this->getConsumerKey(),
                'client_secret' => $this->getConsumerSecret(),
                'redirect_uri' => $this->getReturnUri(),
                'code' => $_GET['code'],
            ];

            $client = new Client();

            try {
                $response = $client->post($this->getAccessTokenUrl(), ['form_params' => $payload]);

                $json = json_decode((string) $response->getBody());
                if (!isset($json->access_token)) {
                    throw new IntegrationException(
                        ExpressForms::t("No 'access_token' present in auth response for Salesforce")
                    );
                }

                $this->setAccessToken($json->access_token);
                $this->setRefreshToken($json->refresh_token);
                $this->setInstance($json->instance_url);

                $this->markForUpdate();
            } catch (RequestException $e) {
                $responseBody = (string) $e->getResponse()->getBody();
                $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

                throw $e;
            }
        }
    }

    public function beforeSaveSettings(): void
    {
        if (!$this->consumerKey || !$this->consumerSecret) {
            $this->consumerKey = null;
            $this->consumerSecret = null;
            $this->accessToken = null;
            $this->refreshToken = null;
            $this->instance = null;
        }
    }

    public function afterSaveSettings(): void
    {
        try {
            if (!$this->getAccessToken()) {
                throw new \Exception('Fetching token');
            }

            $client = $this->generateAuthorizedClient(false);
            $client->get($this->getEndpoint('/'));
        } catch (\Exception $e) {
            $consumerKey = $this->getConsumerKey();
            $consumerSecret = $this->getConsumerSecret();

            if (!$consumerKey || !$consumerSecret) {
                return;
            }

            $payload = [
                'response_type' => 'code',
                'client_id' => $consumerKey,
                'scope' => 'api refresh_token',
                'redirect_uri' => $this->getReturnUri(),
            ];

            header('Location: '.$this->getAuthorizeUrl().'?'.http_build_query($payload));

            exit();
        }
    }

    public function getConsumerKey(): ?string
    {
        return $this->consumerKey;
    }

    public function setConsumerKey(string $consumerKey = null): self
    {
        $this->consumerKey = $consumerKey;

        return $this;
    }

    public function getConsumerSecret(): ?string
    {
        return $this->consumerSecret;
    }

    public function setConsumerSecret(string $consumerSecret = null): self
    {
        $this->consumerSecret = $consumerSecret;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken = null): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken = null): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function isAssignOwner(): bool
    {
        return $this->assignOwner;
    }

    public function setAssignOwner(bool $assignOwner = false): self
    {
        $this->assignOwner = $assignOwner;

        return $this;
    }

    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    public function setSandboxMode(bool $sandboxMode = false): self
    {
        $this->sandboxMode = $sandboxMode;

        return $this;
    }

    public function isCustomUrl(): bool
    {
        return $this->customUrl;
    }

    public function setCustomUrl(bool $customUrl = false): self
    {
        $this->customUrl = $customUrl;

        return $this;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function setInstance(string $instance = null): self
    {
        $this->instance = $instance;

        return $this;
    }

    public function getCloseDate(): ?string
    {
        return $this->closeDate;
    }

    public function setCloseDate(string $closeDate = null): self
    {
        $this->closeDate = $closeDate;

        return $this;
    }

    public function getStageName(): ?string
    {
        return $this->stageName;
    }

    public function setStageName(string $stageName = null): self
    {
        $this->stageName = $stageName;

        return $this;
    }

    public function serializeSettings(): array
    {
        return [
            'consumerKey' => $this->getConsumerKey(),
            'consumerSecret' => $this->getConsumerSecret(),
            'accessToken' => $this->getAccessToken(),
            'refreshToken' => $this->getRefreshToken(),
            'assignOwner' => $this->isAssignOwner(),
            'sandboxMode' => $this->isSandboxMode(),
            'customUrl' => $this->isCustomUrl(),
            'instance' => $this->getInstance(),
            'closeDate' => $this->getCloseDate(),
            'stageName' => $this->getStageName(),
        ];
    }

    /**
     * @return resource[]
     */
    public function fetchResources(): array
    {
        $resources = [
            new Resource($this, self::RESOURCE_LEAD, self::RESOURCE_LEAD),
            new Resource($this, self::RESOURCE_OPPORTUNITY, self::RESOURCE_OPPORTUNITY),
        ];

        $event = new FetchResourcesEvent($this, $resources);
        Event::trigger($this, self::EVENT_FETCH_RESOURCES, $event);

        return $event->getResourceList();
    }

    /**
     * @return ResourceField[]
     */
    public function fetchResourceFields(int|string $resourceId): array
    {
        if (self::RESOURCE_LEAD === $resourceId) {
            return $this->fetchFieldsForLeads();
        }

        if (self::RESOURCE_OPPORTUNITY === $resourceId) {
            return $this->fetchFieldsForOpportunities();
        }

        return [];
    }

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        if (self::RESOURCE_LEAD === $mapping->getResourceId()) {
            return $this->pushLeads($mapping, $postedData);
        }

        if (self::RESOURCE_OPPORTUNITY === $mapping->getResourceId()) {
            return $this->pushOpportunities($mapping, $postedData);
        }

        return false;
    }

    protected function getApiRootUrl(): string
    {
        return $this->instance.'/services/data/v44.0/';
    }

    private function fetchFieldsForLeads(): array
    {
        $client = $this->generateAuthorizedClient();

        try {
            $response = $client->get($this->getEndpoint('/sobjects/Lead/describe'));
        } catch (RequestException $e) {
            $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);

            return [];
        }

        $data = json_decode((string) $response->getBody(), false);

        $fieldList = [];
        foreach ($data->fields as $field) {
            if (!$field->updateable || !empty($field->referenceTo)) {
                continue;
            }

            $fieldObject = new ResourceField(
                $field->label,
                $field->name,
                $field->type,
                !$field->nillable,
                (array) $field
            );

            $fieldList[] = $fieldObject;
        }

        $event = new FetchResourceFieldsEvent($this, self::RESOURCE_LEAD, $fieldList);
        Event::trigger($this, self::EVENT_FETCH_RESOURCE_FIELDS, $event);

        return $event->getResourceFieldsList();
    }

    private function fetchFieldsForOpportunities(): array
    {
        $client = $this->generateAuthorizedClient();

        $fieldEndpoints = [
            self::FIELD_CATEGORY_OPPORTUNITY,
            self::FIELD_CATEGORY_ACCOUNT,
            self::FIELD_CATEGORY_CONTACT,
        ];

        $fieldList = [];
        foreach ($fieldEndpoints as $category) {
            try {
                $response = $client->get($this->getEndpoint("/sobjects/{$category}/describe"));
            } catch (RequestException $e) {
                $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);

                continue;
            }

            $data = json_decode((string) $response->getBody(), false);

            foreach ($data->fields as $field) {
                if (!$field->updateable || !empty($field->referenceTo)) {
                    continue;
                }

                if (\in_array($field->name, ['StageName', 'CloseDate'], true)) {
                    continue;
                }

                $fieldObject = new ResourceField(
                    $field->label,
                    $category.'___'.$field->name,
                    $field->type,
                    !$field->nillable,
                    (array) $field,
                    $category
                );

                $fieldList[] = $fieldObject;
            }
        }

        $event = new FetchResourceFieldsEvent($this, self::RESOURCE_OPPORTUNITY, $fieldList);
        Event::trigger($this, self::EVENT_FETCH_RESOURCE_FIELDS, $event);

        return $event->getResourceFieldsList();
    }

    private function pushLeads(IntegrationMappingInterface $mapping, array $postData = []): bool
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint("/sobjects/{$mapping->getResourceId()}");

        $mappedValues = [];
        $mappedFields = $mapping->getFieldMappings();
        foreach ($mappedFields as $key => $field) {
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
            $response = $client->post(
                $endpoint,
                [
                    'headers' => ['Sforce-Auto-Assign' => $this->isAssignOwner() ? 'TRUE' : 'FALSE'],
                    'json' => $mappedValues,
                ]
            );

            Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));

            return 201 === $response->getStatusCode();
        } catch (RequestException $e) {
            $exceptionResponse = $e->getResponse();
            if (!$exceptionResponse) {
                $this->getLogger()->error($e->getMessage(), ['exception' => $e->getMessage()]);

                return false;
            }

            $responseBody = (string) $exceptionResponse->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            return false;
        }
    }

    private function pushOpportunities(IntegrationMappingInterface $mapping, array $postData = []): bool
    {
        $mappedValues = [
            self::FIELD_CATEGORY_OPPORTUNITY => [],
            self::FIELD_CATEGORY_ACCOUNT => [],
            self::FIELD_CATEGORY_CONTACT => [],
        ];

        foreach ($mapping->getFieldMappings() as $key => $field) {
            $resourceField = $mapping->getResourceFields()->get($key);
            if (null === $resourceField) {
                continue;
            }

            if (preg_match('/^\w+___(.*)$/', $key, $matches)) {
                $key = $matches[1];
            }

            $mappedValues[$resourceField->getCategory()][$key] = $field->getValueAsString();
        }

        $event = new IntegrationValueMappingEvent($mappedValues);
        Event::trigger($this, self::EVENT_AFTER_SET_MAPPING, $event);
        $mappedValues = $event->getMappedValues();

        $client = $this->generateAuthorizedClient();

        try {
            $closeDate = new Carbon($this->getCloseDate());
        } catch (\Exception $e) {
            $closeDate = new Carbon();
        }

        $accountMapping = $mappedValues[self::FIELD_CATEGORY_ACCOUNT] ?? [];
        $contactMapping = $mappedValues[self::FIELD_CATEGORY_CONTACT] ?? [];
        $opportunityMapping = $mappedValues[self::FIELD_CATEGORY_OPPORTUNITY] ?? [];

        $accountName = $accountMapping['Name'] ?? null;
        $contactFirstName = $contactMapping['FirstName'] ?? null;
        $contactLastName = $contactMapping['LastName'] ?? null;
        $contactEmail = $contactMapping['Email'] ?? null;
        $contactName = trim("{$contactFirstName} {$contactLastName}");
        if (empty($accountName)) {
            $accountName = $contactName;
            $accountMapping['Name'] = $accountName;
        }

        $accountRecord = $this->querySingle(
            "SELECT Id
                FROM Account
                WHERE Name = '%s'
                ORDER BY CreatedDate desc
                LIMIT 1",
            [$accountName]
        );

        $contactRecord = null;
        if (!empty($contactEmail)) {
            $contactRecord = $this->querySingle(
                "SELECT Id
                FROM Contact
                WHERE Email = '%s'
                ORDER BY CreatedDate desc
                LIMIT 1",
                [$contactEmail]
            );
        }

        if (!$contactRecord) {
            $contactRecord = $this->querySingle(
                "SELECT Id
                FROM Contact
                WHERE Name = '%s'
                ORDER BY CreatedDate desc
                LIMIT 1",
                [$contactName]
            );
        }

        try {
            if ($accountRecord) {
                $accountEndpoint = $this->getEndpoint('/sobjects/Account/'.$accountRecord->Id);
                $response = $client->patch($accountEndpoint, ['json' => $accountMapping]);
                $accountId = $accountRecord->Id;

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } else {
                $accountEndpoint = $this->getEndpoint('/sobjects/Account');
                $accountResponse = $client->post($accountEndpoint, ['json' => $accountMapping]);
                $accountId = json_decode($accountResponse->getBody(), false)->id;

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($accountResponse));
            }

            $contactMapping['AccountId'] = $accountId;
            if ($contactRecord) {
                $contactEndpoint = $this->getEndpoint('/sobjects/Contact/'.$contactRecord->Id);
                $response = $client->patch($contactEndpoint, ['json' => $contactMapping]);

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } else {
                $contactEndpoint = $this->getEndpoint('/sobjects/Contact');
                $response = $client->post($contactEndpoint, ['json' => $contactMapping]);

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            }

            $opportunityMapping['CloseDate'] = $closeDate->toIso8601ZuluString();
            $opportunityMapping['AccountId'] = $accountId;
            $opportunityMapping['StageName'] = $this->getStageName() ?? 'New Stage';

            $response = $client->post(
                $this->getEndpoint('/sobjects/Opportunity'),
                ['json' => $opportunityMapping]
            );

            Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));

            return 201 === $response->getStatusCode();
        } catch (RequestException $e) {
            $exceptionResponse = $e->getResponse();
            if (!$exceptionResponse) {
                $this->getLogger()->error($e->getMessage(), ['exception' => $e->getMessage()]);

                throw $e;
            }

            $responseBody = (string) $exceptionResponse->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            if (400 === $exceptionResponse->getStatusCode()) {
                $errors = json_decode((string) $exceptionResponse->getBody(), false);

                if (\is_array($errors)) {
                    foreach ($errors as $error) {
                        if ('REQUIRED_FIELD_MISSING' === strtoupper($error->errorCode)) {
                            return false;
                        }
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * URL pointing to the OAuth2 authorization endpoint.
     */
    private function getAuthorizeUrl(): string
    {
        return 'https://'.$this->getLoginUrl().'.salesforce.com/services/oauth2/authorize';
    }

    /**
     * URL pointing to the OAuth2 access token endpoint.
     */
    private function getAccessTokenUrl(): string
    {
        return 'https://'.$this->getLoginUrl().'.salesforce.com/services/oauth2/token';
    }

    private function getLoginUrl(): string
    {
        return $this->isSandboxMode() ? 'test' : 'login';
    }

    private function getReturnUri(): string
    {
        return UrlHelper::cpUrl('express-forms/settings/api-integrations/salesforce');
    }

    private function getRefreshedAccessToken(): string
    {
        if (!$this->getRefreshToken() || !$this->getConsumerSecret() || !$this->getConsumerKey()) {
            $this->getLogger()->warning(
                'Trying to refresh Salesforce access token with no Salesforce credentials present.'
            );

            return 'invalid';
        }

        $client = new Client();
        $payload = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
            'client_id' => $this->getConsumerKey(),
            'client_secret' => $this->getConsumerSecret(),
        ];

        try {
            $response = $client->post($this->getAccessTokenUrl(), ['form_params' => $payload]);

            $json = json_decode((string) $response->getBody(), false);
            if (!isset($json->access_token)) {
                throw new IntegrationException(
                    ExpressForms::t("No 'access_token' present in auth response for Salesforce")
                );
            }

            $this->setAccessToken($json->access_token);
            $this->setInstance($json->instance_url);

            $this->markForUpdate();

            return $this->getAccessToken();
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            return '';
        }
    }

    private function generateAuthorizedClient(bool $refreshTokenIfExpired = true): Client
    {
        $client = new Client(
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        if ($refreshTokenIfExpired) {
            try {
                $endpoint = $this->getEndpoint('/');
                $client->get($endpoint);
            } catch (RequestException $e) {
                if (401 === $e->getCode()) {
                    $client = new Client(
                        [
                            'headers' => [
                                'Authorization' => 'Bearer '.$this->getRefreshedAccessToken(),
                                'Content-Type' => 'application/json',
                            ],
                        ]
                    );
                }
            }
        }

        return $client;
    }

    private function query(string $query, array $params = []): array
    {
        $client = $this->generateAuthorizedClient();

        $params = array_map([$this, 'soqlEscape'], $params);
        $query = sprintf($query, ...$params);

        try {
            $response = $client->get(
                $this->getEndpoint('/query'),
                [
                    'query' => [
                        'q' => $query,
                    ],
                ]
            );

            $result = json_decode($response->getBody(), false);

            if (0 === $result->totalSize || !$result->done) {
                return [];
            }

            return $result->records;
        } catch (RequestException $e) {
            $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);

            return [];
        }
    }

    private function querySingle(string $query, array $params = []): mixed
    {
        $data = $this->query($query, $params);

        if (\count($data) >= 1) {
            return reset($data);
        }

        return null;
    }

    private function soqlEscape(string $str = ''): string
    {
        $characters = [
            '\\',
            '\'',
        ];
        $replacement = [
            '\\\\',
            '\\\'',
        ];

        return str_replace($characters, $replacement, $str);
    }
}
