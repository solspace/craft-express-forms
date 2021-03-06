<?php

namespace Solspace\ExpressForms\integrations\types;

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
use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\MailingListTypeInterface;
use Solspace\ExpressForms\objects\Integrations\Setting;
use yii\base\Event;

class ConstantContact extends AbstractIntegrationType implements MailingListTypeInterface
{
    const FIELD_TARGET_EMAIL = 'constantContactTargetEmail';
    const FIELD_OPT_IN = 'constantContactOptIn';

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $accessToken;

    /** @var string */
    protected $refreshToken;

    public static function getSettingsManifest(): array
    {
        return [
            new Setting('API Key', 'apiKey'),
            new Setting('Secret', 'secret'),
        ];
    }

    public function getName(): string
    {
        return 'Constant Contact';
    }

    public function getHandle(): string
    {
        return 'constant-contact';
    }

    public function getDescription(): string
    {
        return 'Send and map submission data to Constant Contact to subscribe users to a mailing list.';
    }

    public function isEnabled(): bool
    {
        return !empty($this->getAccessToken());
    }

    /**
     * @throws ConnectionFailedException
     * @throws IntegrationException
     */
    public function checkConnection(bool $refreshTokenIfExpired = true): bool
    {
        $client = $this->generateAuthorizedClient($refreshTokenIfExpired);
        $endpoint = $this->getEndpoint('/contact_lists');

        try {
            $response = $client->get($endpoint);
            $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

            return isset($json->lists);
        } catch (RequestException $exception) {
            $responseBody = (string) $exception->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $exception->getMessage()]);

            throw new ConnectionFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getPrevious()
            );
        }
    }

    /**
     * Do something before settings are rendered.
     */
    public function beforeRenderUpdate()
    {
        if (isset($_GET['code'])) {
            $payload = [
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->getReturnUri(),
                'code' => $_GET['code'],
            ];

            $client = new Client();

            try {
                $response = $client->post(
                    $this->getAccessTokenUrl(),
                    [
                        'auth' => [$this->getApiKey(), $this->getSecret()],
                        'form_params' => $payload,
                    ]
                );

                $json = \GuzzleHttp\json_decode((string) $response->getBody());
                if (!isset($json->access_token)) {
                    throw new IntegrationException(
                        ExpressForms::t("No 'access_token' present in auth response for Constant Contact")
                    );
                }

                $this->setAccessToken($json->access_token);
                $this->setRefreshToken($json->refresh_token);
                $this->markForUpdate();
            } catch (RequestException $e) {
                $responseBody = (string) $e->getResponse()->getBody();
                $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

                throw $e;
            }
        }
    }

    /**
     * Do something before settings are saved.
     */
    public function beforeSaveSettings()
    {
        if (!$this->apiKey || !$this->secret) {
            $this->apiKey = null;
            $this->secret = null;
            $this->accessToken = null;
            $this->refreshToken = null;
        }
    }

    /**
     * Perform an OAUTH authorization.
     */
    public function afterSaveSettings()
    {
        try {
            if (!$this->getAccessToken()) {
                throw new ConnectionFailedException('Fetching token');
            }

            $this->checkConnection(true);
        } catch (ConnectionFailedException $e) {
            $apiKey = $this->getApiKey();
            $secret = $this->getSecret();

            if (!$apiKey || !$secret) {
                return false;
            }

            $payload = [
                'response_type' => 'code',
                'client_id' => $apiKey,
                'redirect_uri' => $this->getReturnUri(),
                'scope' => 'contact_data',
            ];

            header('Location: '.$this->getAuthorizeUrl().'?'.http_build_query($payload));

            exit();
        }
    }

    /**
     * @return null|string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey = null): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret = null): self
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken = null): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken = null): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function serializeSettings(): array
    {
        return [
            'apiKey' => $this->getApiKey(),
            'secret' => $this->getSecret(),
            'accessToken' => $this->getAccessToken(),
            'refreshToken' => $this->getRefreshToken(),
        ];
    }

    /**
     * @return resource[]
     */
    public function fetchResources(): array
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contact_lists');

        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new IntegrationException(ExpressForms::t('Could not connect to API endpoint'));
        }

        $status = $response->getStatusCode();
        if (200 !== $status) {
            $this->getLogger()->error(
                'Could not fetch Constant Contact lists',
                ['response' => (string) $response->getBody()]
            );

            throw new IntegrationException(ExpressForms::t('Could not fetch mailing lists'));
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        $lists = [];
        foreach ($json->lists as $list) {
            if (isset($list->list_id, $list->name)) {
                $lists[] = new Resource(
                    $this,
                    $list->name,
                    $list->list_id,
                    (array) $list
                );
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
        $fieldList = [
            new ResourceField('Opt-in Field', self::FIELD_OPT_IN, 'bool', true),
            new ResourceField('Email', self::FIELD_TARGET_EMAIL, 'string', true),
            new ResourceField('First Name', 'first_name', 'string'),
            new ResourceField('Last Name', 'last_name', 'string'),
            new ResourceField('Job Title', 'job_title', 'string'),
            new ResourceField('Company Name', 'company_name', 'string'),
            new ResourceField('Cell Phone', 'mobile', 'phone'),
            new ResourceField('Home Phone', 'home', 'phone'),
            new ResourceField('Fax', 'fax', 'phone'),
        ];

        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contact_custom_fields');

        try {
            $response = $client->get($endpoint);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new IntegrationException(ExpressForms::t('Could not connect to API endpoint'));
        }

        $json = \GuzzleHttp\json_decode((string) $response->getBody(), false);

        if (isset($json->custom_fields)) {
            foreach ($json->custom_fields as $field) {
                $fieldList[] = new ResourceField(
                    $field->label,
                    $field->custom_field_id,
                    $field->type,
                    false,
                    (array) $field
                );
            }
        }

        $event = new FetchResourceFieldsEvent($this, $resourceId, $fieldList);
        Event::trigger($this, self::EVENT_FETCH_RESOURCE_FIELDS, $event);

        return $event->getResourceFieldsList();
    }

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        $client = $this->generateAuthorizedClient();

        $optIn = $mapping->getField(self::FIELD_OPT_IN);
        $recipient = $mapping->getField(self::FIELD_TARGET_EMAIL);

        if (!$recipient || ($optIn && false === (bool) $optIn->getValue())) {
            return false;
        }

        $email = $recipient->getValueAsString();

        $mappedValues = ['custom_fields' => [], 'phone_numbers' => []];
        $mappedFields = $mapping->getFieldMappings();
        foreach ($mappedFields as $key => $field) {
            if (\in_array($key, [self::FIELD_OPT_IN, self::FIELD_TARGET_EMAIL], true)) {
                continue;
            }

            $resourceField = $mapping->getResourceFields()->get($key);
            if (null === $resourceField) {
                continue;
            }

            $value = $field->getValueAsString();

            switch ($resourceField->getType()) {
                case 'date':
                    try {
                        $value = new \DateTime($value);
                    } catch (\Exception $e) {
                        $value = new \DateTime();
                    }

                    $value = $value->format('Y-m-d');

                    break;

                case 'phone':
                    $mappedValues['phone_numbers'][] = [
                        'phone_number' => $value,
                        'kind' => $key,
                    ];

                    continue 2;

                    break;
            }

            if (!empty($resourceField->getSettings()) && $value) {
                $mappedValues['custom_fields'][] = [
                    'custom_field_id' => $key,
                    'value' => $value,
                ];
            } else {
                $mappedValues[$key] = $value;
            }
        }

        if (empty($mappedValues['custom_fields'])) {
            unset($mappedValues['custom_fields']);
        }

        if (empty($mappedValues['phone_numbers'])) {
            unset($mappedValues['phone_numbers']);
        }

        $event = new IntegrationValueMappingEvent($mappedValues);
        Event::trigger($this, self::EVENT_AFTER_SET_MAPPING, $event);

        $mappedValues = $event->getMappedValues();

        try {
            $data = array_merge(
                [
                    'email_address' => [
                        'address' => $email,
                        'permission_to_send' => 'implicit',
                    ],
                    'create_source' => 'Contact',
                    'list_memberships' => [$mapping->getResourceId()],
                ],
                $mappedValues
            );

            $response = $client->post($this->getEndpoint('/contacts'), ['json' => $data]);
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            throw new IntegrationException(
                ExpressForms::t('Could not connect to API endpoint')
            );
        }

        $status = $response->getStatusCode();
        if (201 !== $status) {
            $this->getLogger()->error('Could not add contacts to list', ['response' => (string) $response->getBody()]);

            throw new IntegrationException(
                ExpressForms::t('Could not add emails to lists')
            );
        }

        Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));

        return 201 === $status;
    }

    /**
     * Returns the API root url without endpoints specified.
     */
    protected function getApiRootUrl(): string
    {
        return 'https://api.cc.email/v3';
    }

    private function getReturnUri(): string
    {
        return UrlHelper::cpUrl('express-forms/settings/api-integrations/'.$this->getHandle());
    }

    /**
     * URL pointing to the OAuth2 authorization endpoint.
     */
    private function getAuthorizeUrl(): string
    {
        return 'https://api.cc.email/v3/idfed';
    }

    /**
     * URL pointing to the OAuth2 access token endpoint.
     */
    private function getAccessTokenUrl(): string
    {
        return 'https://idfed.constantcontact.com/as/token.oauth2';
    }

    /**
     * @throws IntegrationException
     */
    private function getRefreshedAccessToken(): string
    {
        if (!$this->getRefreshToken() || !$this->getApiKey() || !$this->getSecret()) {
            $this->getLogger()->warning(
                'Trying to refresh Constant Contact access token with no credentials present'
            );

            return 'invalid';
        }

        $client = new Client();
        $payload = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
        ];

        try {
            $response = $client->post(
                $this->getAccessTokenUrl(),
                [
                    'auth' => [$this->getApiKey(), $this->getSecret()],
                    'form_params' => $payload,
                ]
            );

            $json = \GuzzleHttp\json_decode((string) $response->getBody());
            if (!isset($json->access_token)) {
                throw new IntegrationException(
                    ExpressForms::t("No 'access_token' present in auth response for Constant Contact")
                );
            }

            $this->setAccessToken($json->access_token);
            $this->setRefreshToken($json->refresh_token);
            $this->markForUpdate();

            return $this->getAccessToken();
        } catch (RequestException $e) {
            $responseBody = (string) $e->getResponse()->getBody();
            $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * @throws IntegrationException
     */
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
                $this->checkConnection(false);
            } catch (ConnectionFailedException $e) {
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
}
