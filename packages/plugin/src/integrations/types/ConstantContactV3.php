<?php

namespace Solspace\ExpressForms\integrations\types;

use craft\helpers\App;
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

class ConstantContactV3 extends AbstractIntegrationType implements MailingListTypeInterface
{
    public const FIELD_TARGET_EMAIL = 'constantContactTargetEmail';
    public const FIELD_OPT_IN = 'constantContactOptIn';

    protected ?string $clientId = null;
    protected ?string $clientSecret = null;
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;

    public static function getSettingsManifest(): array
    {
        return [
            new Setting('Client ID', 'clientId'),
            new Setting('Client Secret', 'clientSecret'),
        ];
    }

    public function getName(): string
    {
        return 'Constant Contact (v3)';
    }

    public function getHandle(): string
    {
        return 'constant-contact-v3';
    }

    public function getDescription(): string
    {
        return 'Send and map submission data to Constant Contact to subscribe users to a mailing list.';
    }

    public function isEnabled(): bool
    {
        return !empty($this->getAccessToken());
    }

    public function checkConnection(bool $refreshTokenIfExpired = true): bool
    {
        $client = $this->generateAuthorizedClient($refreshTokenIfExpired);
        $endpoint = $this->getEndpoint('/contact_lists');

        try {
            $response = $client->get($endpoint);
            $json = json_decode((string) $response->getBody(), false);

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

    public function beforeRenderUpdate(): void
    {
        if (isset($_GET['code'])) {
            $payload = [
                'grant_type' => 'authorization_code',
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'redirect_uri' => $this->getRedirectUri(),
                'code' => $_GET['code'],
            ];

            $client = new Client();

            try {
                $response = $client->post(
                    $this->getAccessTokenUrl(),
                    ['form_params' => $payload]
                );

                $json = json_decode((string) $response->getBody());
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

    public function beforeSaveSettings(): void
    {
        if (!$this->clientId || !$this->clientSecret) {
            $this->clientId = null;
            $this->clientSecret = null;
            $this->accessToken = null;
            $this->refreshToken = null;
        }
    }

    public function afterSaveSettings(): void
    {
        try {
            if (!$this->getAccessToken()) {
                throw new ConnectionFailedException('Fetching token');
            }

            $this->checkConnection(true);
        } catch (ConnectionFailedException $e) {
            $clientId = $this->getClientId();
            $clientSecret = $this->getClientSecret();

            if (!$clientId || !$clientSecret) {
                return;
            }

            $payload = [
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $this->getRedirectUri(),
                'scope' => 'contact_data offline_access',
                'state' => session_id(),
            ];

            header('Location: '.$this->getAuthorizeUrl().'?'.http_build_query($payload));

            exit;
        }
    }

    public function getClientId(): ?string
    {
        return App::parseEnv($this->clientId);
    }

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return App::parseEnv($this->clientSecret);
    }

    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

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

    public function serializeSettings(): array
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
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

        $json = json_decode((string) $response->getBody(), false);

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

    public function fetchResourceFields(int|string $resourceId): array
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

        $json = json_decode((string) $response->getBody(), false);

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

    protected function getApiRootUrl(): string
    {
        return 'https://api.cc.email/v3';
    }

    private function getRedirectUri(): string
    {
        return UrlHelper::cpUrl('express-forms/settings/api-integrations/'.$this->getHandle());
    }

    private function getAuthorizeUrl(): string
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/authorize';
    }

    private function getAccessTokenUrl(): string
    {
        return 'https://authz.constantcontact.com/oauth2/default/v1/token';
    }

    private function getRefreshedAccessToken(): string
    {
        if (!$this->getRefreshToken() || !$this->getClientId() || !$this->getClientSecret()) {
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
                    'auth' => [$this->getClientId(), $this->getClientSecret()],
                    'form_params' => $payload,
                ]
            );

            $json = json_decode((string) $response->getBody());
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
