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
    const RESOURCE_LEAD        = 'Lead';
    const RESOURCE_OPPORTUNITY = 'Opportunity';

    const FIELD_CATEGORY_OPPORTUNITY = 'Opportunity';
    const FIELD_CATEGORY_ACCOUNT     = 'Account';
    const FIELD_CATEGORY_CONTACT     = 'Contact';

    /** @var string */
    protected $consumerKey;

    /** @var string */
    protected $consumerSecret;

    /** @var string */
    protected $accessToken;

    /** @var string */
    protected $refreshToken;

    /** @var bool */
    protected $assignOwner = false;

    /** @var bool */
    protected $sandboxMode = false;

    /** @var bool */
    protected $customUrl = false;

    /** @var string */
    protected $instance;

    /** @var string */
    protected $closeDate;

    /** @var string */
    protected $stageName;

    /**
     * @return array
     */
    public static function getSettingsManifest(): array
    {
        return [
            new Setting('Consumer Key', 'consumerKey', Setting::TYPE_TEXT),
            new Setting('Consumer Secret', 'consumerSecret', Setting::TYPE_TEXT),
            new Setting('Assign Owner?', 'assignOwner', Setting::TYPE_BOOLEAN),
            new Setting('Sandbox mode?', 'sandboxMode', Setting::TYPE_BOOLEAN),
            new Setting('Using custom URL?', 'customUrl', Setting::TYPE_BOOLEAN),
            new Setting('Close Date (required for Opportunity only)', 'closeDate', Setting::TYPE_TEXT),
            new Setting('Stage Name (required for Opportunity only)', 'stageName', Setting::TYPE_TEXT),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Salesforce';
    }

    /**
     * @return string
     */
    public function getHandle(): string
    {
        return 'salesforce';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Send and map submission data to your choice of Salesforce Lead or Opportunity, Account and Contact resources.';
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !empty($this->getAccessToken());
    }

    /**
     * @return bool
     */
    public function checkConnection(): bool
    {
        $client   = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/');

        try {
            $response = $client->get($endpoint);
            $json     = \GuzzleHttp\json_decode((string) $response->getBody(), true);

            return !empty($json);
        } catch (RequestException $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Do something before settings are rendered
     */
    public function beforeRenderUpdate()
    {
        if (isset($_GET['code'])) {
            $payload = [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->getConsumerKey(),
                'client_secret' => $this->getConsumerSecret(),
                'redirect_uri'  => $this->getReturnUri(),
                'code'          => $_GET['code'],
            ];

            $client = new Client();

            try {
                $response = $client->post($this->getAccessTokenUrl(), ['form_params' => $payload]);

                $json = \GuzzleHttp\json_decode((string) $response->getBody());
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

    /**
     * Do something before settings are saved
     */
    public function beforeSaveSettings()
    {
        if (!$this->consumerKey || !$this->consumerSecret) {
            $this->consumerKey    = null;
            $this->consumerSecret = null;
            $this->accessToken    = null;
            $this->refreshToken   = null;
            $this->instance       = null;
        }
    }

    /**
     * Perform an OAUTH authorization
     */
    public function afterSaveSettings()
    {
        try {
            if (!$this->getAccessToken()) {
                throw new \Exception('Fetching token');
            }

            $client = $this->generateAuthorizedClient(false);
            $client->get($this->getEndpoint('/'));
        } catch (\Exception $e) {
            $consumerKey    = $this->getConsumerKey();
            $consumerSecret = $this->getConsumerSecret();

            if (!$consumerKey || !$consumerSecret) {
                return false;
            }

            $payload = [
                'response_type' => 'code',
                'client_id'     => $consumerKey,
                'scope'         => 'api refresh_token',
                'redirect_uri'  => $this->getReturnUri(),
            ];

            header('Location: ' . $this->getAuthorizeUrl() . '?' . http_build_query($payload));
            die();
        }
    }

    /**
     * @return string|null
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @param string $consumerKey
     *
     * @return Salesforce
     */
    public function setConsumerKey(string $consumerKey = null): Salesforce
    {
        $this->consumerKey = $consumerKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }

    /**
     * @param string $consumerSecret
     *
     * @return Salesforce
     */
    public function setConsumerSecret(string $consumerSecret = null): Salesforce
    {
        $this->consumerSecret = $consumerSecret;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return Salesforce
     */
    public function setAccessToken(string $accessToken = null): Salesforce
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return Salesforce
     */
    public function setRefreshToken(string $refreshToken = null): Salesforce
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAssignOwner(): bool
    {
        return $this->assignOwner;
    }

    /**
     * @param bool $assignOwner
     *
     * @return Salesforce
     */
    public function setAssignOwner(bool $assignOwner = false): Salesforce
    {
        $this->assignOwner = $assignOwner;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    /**
     * @param bool $sandboxMode
     *
     * @return Salesforce
     */
    public function setSandboxMode(bool $sandboxMode = false): Salesforce
    {
        $this->sandboxMode = $sandboxMode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCustomUrl(): bool
    {
        return $this->customUrl;
    }

    /**
     * @param bool $customUrl
     *
     * @return Salesforce
     */
    public function setCustomUrl(bool $customUrl = false): Salesforce
    {
        $this->customUrl = $customUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param string $instance
     *
     * @return Salesforce
     */
    public function setInstance(string $instance = null): Salesforce
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCloseDate()
    {
        return $this->closeDate;
    }

    /**
     * @param string $closeDate
     *
     * @return Salesforce
     */
    public function setCloseDate(string $closeDate = null): Salesforce
    {
        $this->closeDate = $closeDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStageName()
    {
        return $this->stageName;
    }

    /**
     * @param string $stageName
     *
     * @return Salesforce
     */
    public function setStageName(string $stageName = null): Salesforce
    {
        $this->stageName = $stageName;

        return $this;
    }

    /**
     * @return array
     */
    public function serializeSettings(): array
    {
        return [
            'consumerKey'    => $this->getConsumerKey(),
            'consumerSecret' => $this->getConsumerSecret(),
            'accessToken'    => $this->getAccessToken(),
            'refreshToken'   => $this->getRefreshToken(),
            'assignOwner'    => $this->isAssignOwner(),
            'sandboxMode'    => $this->isSandboxMode(),
            'customUrl'      => $this->isCustomUrl(),
            'instance'       => $this->getInstance(),
            'closeDate'      => $this->getCloseDate(),
            'stageName'      => $this->getStageName(),
        ];
    }

    /**
     * @return Resource[]
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
     * @param int|string $resourceId
     *
     * @return ResourceField[]
     */
    public function fetchResourceFields($resourceId): array
    {
        if ($resourceId === self::RESOURCE_LEAD) {
            return $this->fetchFieldsForLeads();
        }

        if ($resourceId === self::RESOURCE_OPPORTUNITY) {
            return $this->fetchFieldsForOpportunities();
        }

        return [];
    }

    /**
     * @return array
     */
    private function fetchFieldsForLeads(): array
    {
        $client = $this->generateAuthorizedClient();

        try {
            $response = $client->get($this->getEndpoint('/sobjects/Lead/describe'));
        } catch (RequestException $e) {
            $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);

            return [];
        }

        $data = \GuzzleHttp\json_decode((string) $response->getBody(), false);

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

    /**
     * @return array
     */
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
                $response = $client->get($this->getEndpoint("/sobjects/$category/describe"));
            } catch (RequestException $e) {
                $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);
                continue;
            }

            $data = \GuzzleHttp\json_decode((string) $response->getBody(), false);

            foreach ($data->fields as $field) {
                if (!$field->updateable || !empty($field->referenceTo)) {
                    continue;
                }

                if (in_array($field->name, ['StageName', 'CloseDate'], true)) {
                    continue;
                }

                $fieldObject = new ResourceField(
                    $field->label,
                    $category . '___' . $field->name,
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

    /**
     * @param IntegrationMappingInterface $mapping
     * @param array                       $postedData
     *
     * @return bool
     */
    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        if ($mapping->getResourceId() === self::RESOURCE_LEAD) {
            return $this->pushLeads($mapping, $postedData);
        }

        if ($mapping->getResourceId() === self::RESOURCE_OPPORTUNITY) {
            return $this->pushOpportunities($mapping, $postedData);
        }

        return false;
    }

    /**
     * @param IntegrationMappingInterface $mapping
     * @param array                       $postData
     *
     * @return bool
     */
    private function pushLeads(IntegrationMappingInterface $mapping, array $postData = []): bool
    {
        $client   = $this->generateAuthorizedClient();
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
                    'json'    => $mappedValues,
                ]
            );

            Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));

            return $response->getStatusCode() === 201;
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
            self::FIELD_CATEGORY_ACCOUNT     => [],
            self::FIELD_CATEGORY_CONTACT     => [],
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

        $accountMapping     = $mappedValues[self::FIELD_CATEGORY_ACCOUNT] ?? [];
        $contactMapping     = $mappedValues[self::FIELD_CATEGORY_CONTACT] ?? [];
        $opportunityMapping = $mappedValues[self::FIELD_CATEGORY_OPPORTUNITY] ?? [];

        $accountName      = $accountMapping['Name'] ?? null;
        $contactFirstName = $contactMapping['FirstName'] ?? null;
        $contactLastName  = $contactMapping['LastName'] ?? null;
        $contactEmail     = $contactMapping['Email'] ?? null;
        $contactName      = trim("$contactFirstName $contactLastName");
        if (empty($accountName)) {
            $accountName            = $contactName;
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
                $accountEndpoint = $this->getEndpoint('/sobjects/Account/' . $accountRecord->Id);
                $response        = $client->patch($accountEndpoint, ['json' => $accountMapping]);
                $accountId       = $accountRecord->Id;

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } else {
                $accountEndpoint = $this->getEndpoint('/sobjects/Account');
                $accountResponse = $client->post($accountEndpoint, ['json' => $accountMapping]);
                $accountId       = \GuzzleHttp\json_decode($accountResponse->getBody(), false)->id;

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($accountResponse));
            }

            $contactMapping['AccountId'] = $accountId;
            if ($contactRecord) {
                $contactEndpoint = $this->getEndpoint('/sobjects/Contact/' . $contactRecord->Id);
                $response        = $client->patch($contactEndpoint, ['json' => $contactMapping]);

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } else {
                $contactEndpoint = $this->getEndpoint('/sobjects/Contact');
                $response        = $client->post($contactEndpoint, ['json' => $contactMapping]);

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

            return $response->getStatusCode() === 201;
        } catch (RequestException $e) {
            $exceptionResponse = $e->getResponse();
            if (!$exceptionResponse) {
                $this->getLogger()->error($e->getMessage(), ['exception' => $e->getMessage()]);

                throw $e;
            }

            $responseBody = (string) $exceptionResponse->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            if ($exceptionResponse->getStatusCode() === 400) {
                $errors = \GuzzleHttp\json_decode((string) $exceptionResponse->getBody(), false);

                if (\is_array($errors)) {
                    foreach ($errors as $error) {
                        if (strtoupper($error->errorCode) === 'REQUIRED_FIELD_MISSING') {
                            return false;
                        }
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * @return string
     */
    protected function getApiRootUrl(): string
    {
        return $this->instance . '/services/data/v44.0/';
    }

    /**
     * URL pointing to the OAuth2 authorization endpoint
     *
     * @return string
     */
    private function getAuthorizeUrl(): string
    {
        return 'https://' . $this->getLoginUrl() . '.salesforce.com/services/oauth2/authorize';
    }

    /**
     * URL pointing to the OAuth2 access token endpoint
     *
     * @return string
     */
    private function getAccessTokenUrl(): string
    {
        return 'https://' . $this->getLoginUrl() . '.salesforce.com/services/oauth2/token';
    }

    /**
     * @return string
     */
    private function getLoginUrl(): string
    {
        return $this->isSandboxMode() ? 'test' : 'login';
    }

    /**
     * @return string
     */
    private function getReturnUri(): string
    {
        return UrlHelper::cpUrl('express-forms/settings/api-integrations/salesforce');
    }

    /**
     * @return string
     * @throws IntegrationException
     */
    private function getRefreshedAccessToken(): string
    {
        if (!$this->getRefreshToken() || !$this->getConsumerSecret() || !$this->getConsumerKey()) {
            $this->getLogger()->warning(
                'Trying to refresh Salesforce access token with no Salesforce credentials present.'
            );

            return 'invalid';
        }

        $client  = new Client();
        $payload = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
            'client_id'     => $this->getConsumerKey(),
            'client_secret' => $this->getConsumerSecret(),
        ];

        try {
            $response = $client->post($this->getAccessTokenUrl(), ['form_params' => $payload]);

            $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);
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

    /**
     * @param bool $refreshTokenIfExpired
     *
     * @return Client
     */
    private function generateAuthorizedClient(bool $refreshTokenIfExpired = true): Client
    {
        $client = new Client(
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type'  => 'application/json',
                ],
            ]
        );

        if ($refreshTokenIfExpired) {
            try {
                $endpoint = $this->getEndpoint('/');
                $client->get($endpoint);
            } catch (RequestException $e) {
                if ($e->getCode() === 401) {
                    $client = new Client(
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->getRefreshedAccessToken(),
                                'Content-Type'  => 'application/json',
                            ],
                        ]
                    );
                }
            }
        }

        return $client;
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return mixed
     */
    private function query(string $query, array $params = []): array
    {
        $client = $this->generateAuthorizedClient();

        $params = array_map([$this, 'soqlEscape'], $params);
        $query  = sprintf($query, ...$params);

        try {
            $response = $client->get(
                $this->getEndpoint('/query'),
                [
                    'query' => [
                        'q' => $query,
                    ],
                ]
            );

            $result = \GuzzleHttp\json_decode($response->getBody(), false);

            if ($result->totalSize === 0 || !$result->done) {
                return [];
            }

            return $result->records;
        } catch (RequestException $e) {
            $this->getLogger()->error($e->getMessage(), ['response' => $e->getResponse()]);

            return [];
        }
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return mixed|null
     */
    private function querySingle(string $query, array $params = [])
    {
        $data = $this->query($query, $params);

        if (\count($data) >= 1) {
            return reset($data);
        }

        return null;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private function soqlEscape(string $str = ''): string
    {
        $characters  = [
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
