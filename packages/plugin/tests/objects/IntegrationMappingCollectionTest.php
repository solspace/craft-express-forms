<?php

namespace Solspace\Tests\ExpressForms\objects;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\integrations\IntegrationMappingInterface;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use Solspace\ExpressForms\objects\Collections\IntegrationMappingCollection;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

/**
 * @internal
 * @coversNothing
 */
class IntegrationMappingCollectionTest extends TestCase
{
    private $type;

    protected function setUp(): void
    {
        $this->type = $this->createMock(IntegrationTypeInterface::class);
    }

    public function testCountingCollection()
    {
        $collection = new IntegrationMappingCollection();
        $collection->addMapping(new TestMapping('one', $this->type));
        $collection->addMapping(new TestMapping('two', $this->type));
        $collection->addMapping(new TestMapping('three', $this->type));

        self::assertCount(3, $collection);
    }

    public function testAccessByHandle()
    {
        $collection = new IntegrationMappingCollection();
        $collection->addMapping(new TestMapping('one', $this->type));
        $collection->addMapping(new TestMapping('two', $this->type));
        $collection->addMapping(new TestMapping('three', $this->type));

        self::assertSame('two', $collection['two']->getHandle());
    }

    public function testStoresByHandle()
    {
        $collection = new IntegrationMappingCollection();
        $collection->addMapping(new TestMapping('one', $this->type));
        $collection->addMapping(new TestMapping('same', $this->type));
        $collection->addMapping(new TestMapping('same', $this->type));
        $collection->addMapping(new TestMapping('same', $this->type));

        self::assertCount(2, $collection);
    }

    public function testGet()
    {
        $collection = new IntegrationMappingCollection();

        $integration = new TestMapping('one', $this->type);
        $collection->addMapping($integration);
        $collection->addMapping(new TestMapping('two', $this->type));

        self::assertSame($integration, $collection->get('one'));
    }

    public function testGetNonExisting()
    {
        $collection = new IntegrationMappingCollection();

        self::assertNull($collection->get('one'));
    }
}

class TestMapping implements IntegrationMappingInterface
{
    private $handle;
    private $type;

    public function __construct(string $handle, IntegrationTypeInterface $type)
    {
        $this->handle = $handle;
        $this->type = $type;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getType(): IntegrationTypeInterface
    {
        return $this->type;
    }

    public function getResourceFields(): ResourceFieldCollection
    {
        return new ResourceFieldCollection();
    }

    public function getFieldMappings(): array
    {
        return [];
    }

    public function getField(string $mappingHandle)
    {
        return null;
    }

    public function jsonSerialize()
    {
        return [];
    }

    public function pushData(array $postedData): bool
    {
        return true;
    }

    public function getResourceId(): string
    {
        return '';
    }
}
