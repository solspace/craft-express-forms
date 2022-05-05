<?php

namespace Solspace\ExpressForms\integrations\types;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Solspace\ExpressForms\events\integrations\FetchResourceFieldsEvent;
use Solspace\ExpressForms\events\integrations\FetchResourcesEvent;
use Solspace\ExpressForms\events\integrations\IntegrationValueMappingEvent;
use Solspace\ExpressForms\events\integrations\PushResponseEvent;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\integrations\AbstractIntegrationType;
use Solspace\ExpressForms\integrations\CrmTypeInterface;
use Solspace\ExpressForms\integrations\dto\Resource;
use Solspace\ExpressForms\integrations\dto\ResourceField;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\objects\Integrations\Setting;
use yii\base\Event;

class HubSpot extends AbstractIntegrationType implements CrmTypeInterface
{
    public const RESOURCE_DEAL_COMPANY_CONTACT = 'deal_company_contact';

    protected ?string $apiKey = null;

    public static function getSettingsManifest(): array
    {
        return [
            new Setting('API Key', 'apiKey'),
        ];
    }

    public function getName(): string
    {
        return 'HubSpot';
    }

    public function getHandle(): string
    {
        return 'hubspot';
    }

    public function getDescription(): string
    {
        return 'Send and map submission data to HubSpot Deals, Contacts and Companies resources.';
    }

    public function isEnabled(): bool
    {
        return !empty($this->getApiKey());
    }

    public function checkConnection(): bool
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/contacts/v1/lists/all/contacts/all');

        try {
            $response = $client->get($endpoint);
            $json = json_decode((string) $response->getBody(), true);

            return isset($json['contacts']);
        } catch (RequestException $e) {
            throw new ConnectionFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey = null): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function serializeSettings(): array
    {
        return [
            'apiKey' => $this->getApiKey(),
        ];
    }

    /**
     * @return resource[]
     */
    public function fetchResources(): array
    {
        $resources = [
            new Resource($this, 'Deal, Company & Contact', self::RESOURCE_DEAL_COMPANY_CONTACT),
        ];

        $event = new FetchResourcesEvent($this, $resources);
        Event::trigger($this, self::EVENT_FETCH_RESOURCES, $event);

        return $event->getResourceList();
    }

    public function fetchResourceFields(int|string $resourceId): array
    {
        $fieldList = [];

        if (self::RESOURCE_DEAL_COMPANY_CONTACT === $resourceId) {
            $this->extractCustomFields(
                '/properties/v1/deals/properties/',
                'Deal',
                $fieldList
            );

            $this->extractCustomFields(
                '/properties/v1/contacts/properties/',
                'Contact',
                $fieldList
            );

            $this->extractCustomFields(
                '/properties/v1/companies/properties/',
                'Company',
                $fieldList
            );
        }

        $event = new FetchResourceFieldsEvent($this, $resourceId, $fieldList);
        Event::trigger($this, self::EVENT_FETCH_RESOURCE_FIELDS, $event);

        return $event->getResourceFieldsList();
    }

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        $client = $this->generateAuthorizedClient();
        $endpoint = $this->getEndpoint('/deals/v1/deal/');

        $dealProps = [];
        $contactProps = [];
        $companyProps = [];

        $mappedFields = $mapping->getFieldMappings();
        foreach ($mappedFields as $key => $field) {
            $resourceField = $mapping->getResourceFields()->get($key);
            if (null === $resourceField) {
                continue;
            }

            $value = $field->getValueAsString();
            $handle = $resourceField->getHandle();

            switch ($resourceField->getCategory()) {
                case 'Contact':
                    $contactProps[] = ['value' => $value, 'property' => $handle];

                    break;

                case 'Company':
                    $companyProps[] = ['value' => $value, 'name' => $handle];

                    break;

                case 'Deal':
                    $dealProps[] = ['value' => $value, 'name' => $handle];

                    break;
            }
        }

        $event = new IntegrationValueMappingEvent(
            [
                'contactProps' => $contactProps,
                'companyProps' => $companyProps,
                'dealProps' => $dealProps,
            ]
        );
        Event::trigger($this, self::EVENT_AFTER_SET_MAPPING, $event);

        $mappedValues = $event->getMappedValues();

        $contactProps = $mappedValues['contactProps'] ?? [];
        $companyProps = $mappedValues['companyProps'] ?? [];
        $dealProps = $mappedValues['dealProps'] ?? [];

        $contactId = null;
        if ($contactProps) {
            try {
                $response = $client->post(
                    $this->getEndpoint('/contacts/v1/contact'),
                    ['json' => ['properties' => $contactProps]]
                );

                $json = json_decode((string) $response->getBody(), false);
                if (isset($json->vid)) {
                    $contactId = $json->vid;
                }

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } catch (RequestException $e) {
                if ($e->getResponse()) {
                    $json = json_decode((string) $e->getResponse()->getBody(), false);
                    if (isset($json->error, $json->identityProfile) && 'CONTACT_EXISTS' === $json->error) {
                        $contactId = $json->identityProfile->vid;
                    } else {
                        $responseBody = (string) $e->getResponse()->getBody();

                        $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);
                    }
                }
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }

        $companyId = null;
        if ($companyProps) {
            try {
                $response = $client->post(
                    $this->getEndpoint('companies/v2/companies'),
                    ['json' => ['properties' => $companyProps]]
                );

                $json = json_decode((string) $response->getBody(), false);
                if (isset($json->companyId)) {
                    $companyId = $json->companyId;
                }

                Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));
            } catch (RequestException $e) {
                $responseBody = (string) $e->getResponse()->getBody();

                $this->getLogger()->error($responseBody, ['exception' => $e->getMessage()]);
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }

        $deal = [
            'properties' => $dealProps,
        ];

        if ($companyId || $contactId) {
            $deal['associations'] = [];

            if ($companyId) {
                $deal['associations']['associatedCompanyIds'] = [$companyId];
            }

            if ($contactId) {
                $deal['associations']['associatedVids'] = [$contactId];
            }
        }

        $response = $client->post($endpoint, ['json' => $deal]);

        Event::trigger($this, self::EVENT_AFTER_RESPONSE, new PushResponseEvent($response));

        return 200 === $response->getStatusCode();
    }

    protected function getApiRootUrl(): string
    {
        return 'https://api.hubapi.com/';
    }

    private function generateAuthorizedClient(): Client
    {
        return new Client(['query' => ['hapikey' => $this->getApiKey()]]);
    }

    private function extractCustomFields(string $endpoint, string $dataType, array &$fieldList): void
    {
        $client = $this->generateAuthorizedClient();
        $response = $client->get($this->getEndpoint($endpoint));

        $data = json_decode((string) $response->getBody(), false);

        foreach ($data as $field) {
            if (
                $field->readOnlyValue
                || $field->hidden
                || $field->calculated
                || $field->deleted
                || 'socialmediainformation' === $field->groupName
            ) {
                continue;
            }

            $fieldObject = new ResourceField(
                $field->label,
                $field->name,
                $field->type,
                false,
                (array) $field,
                $dataType
            );

            $fieldList[] = $fieldObject;
        }
    }
}
