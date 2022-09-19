<?php

namespace Solspace\Tests\ExpressForms\events\integrations;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\events\integrations\RegisterIntegrationTypes;
use Solspace\ExpressForms\integrations\AbstractIntegrationType;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class RegisterIntegrationTypesTest extends TestCase
{
    public function testGetTypes()
    {
        $event = new RegisterIntegrationTypes([]);
        $event->addType(IntegrationType1::class);
        $event->addType(IntegrationType2::class);

        self::assertCount(2, $event->getTypes());
    }

    public function testGetTypesWithConfig()
    {
        $event = new RegisterIntegrationTypes(
            [
                IntegrationType1::class => ['overlap' => 'test'],
                IntegrationType2::class => ['different' => 'test', 'non-existing' => 'value'],
            ]
        );
        $event->addType(IntegrationType1::class);
        $event->addType(IntegrationType2::class);

        self::assertSame('test', $event->getTypes()[0]->getOverlap());
        self::assertNull($event->getTypes()[0]->getFirst());

        self::assertSame('test', $event->getTypes()[1]->getDifferent());
        self::assertNull($event->getTypes()[1]->getOverlap());
    }

    public function testConfigWithNonExistingValues()
    {
        $event = new RegisterIntegrationTypes(
            [
                IntegrationType1::class => ['overlap' => 'test', 'different' => 'test', 'non-existing' => 'value'],
            ]
        );
        $event->addType(IntegrationType1::class);

        self::assertSame('test', $event->getTypes()[0]->getOverlap());
        self::assertNull($event->getTypes()[0]->getFirst());
    }

    public function testConfigWithPrivateValue()
    {
        $event = new RegisterIntegrationTypes(
            [
                IntegrationType1::class => ['overlap' => 'test', 'different' => 'test', 'inaccessible' => 'value'],
            ]
        );
        $event->addType(IntegrationType1::class);

        self::assertSame('test', $event->getTypes()[0]->getOverlap());
        self::assertNull($event->getTypes()[0]->getFirst());
    }
}

class IntegrationType1 extends AbstractIntegrationType
{
    protected $overlap;
    protected $first;

    private $inaccessible;

    public function getName(): string
    {
    }

    public static function getSettingsManifest(): array
    {
    }

    public function getHandle(): string
    {
    }

    public function isEnabled(): bool
    {
    }

    public function checkConnection(): bool
    {
    }

    public function serializeSettings(): array
    {
    }

    public function getDescription(): string
    {
    }

    public function fetchResources(): array
    {
        return [];
    }

    public function fetchResourceFields($resourceId): array
    {
        return [];
    }

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getOverlap()
    {
        return $this->overlap;
    }

    /**
     * @param mixed $overlap
     */
    public function setOverlap($overlap): self
    {
        $this->overlap = $overlap;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * @param mixed $first
     */
    public function setFirst($first): self
    {
        $this->first = $first;

        return $this;
    }

    protected function getApiRootUrl(): string
    {
    }
}

class IntegrationType2 extends AbstractIntegrationType
{
    protected $overlap;
    protected $different;

    public function getName(): string
    {
    }

    public static function getSettingsManifest(): array
    {
    }

    public function getHandle(): string
    {
    }

    public function isEnabled(): bool
    {
    }

    public function checkConnection(): bool
    {
    }

    public function serializeSettings(): array
    {
    }

    public function getDescription(): string
    {
    }

    public function fetchResources(): array
    {
        return [];
    }

    public function fetchResourceFields($resourceId): array
    {
        return [];
    }

    public function pushData(IntegrationMappingInterface $mapping, array $postedData = []): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getOverlap()
    {
        return $this->overlap;
    }

    /**
     * @param mixed $overlap
     */
    public function setOverlap($overlap): self
    {
        $this->overlap = $overlap;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDifferent()
    {
        return $this->different;
    }

    /**
     * @param mixed $different
     */
    public function setDifferent($different): self
    {
        $this->different = $different;

        return $this;
    }

    protected function getApiRootUrl(): string
    {
    }
}
