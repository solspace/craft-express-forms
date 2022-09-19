<?php

namespace Solspace\Tests\ExpressForms\integrations;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\fields\FieldInterface;
use Solspace\ExpressForms\integrations\IntegrationMapping;
use Solspace\ExpressForms\integrations\IntegrationTypeInterface;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\objects\Collections\ResourceFieldCollection;

/**
 * @internal
 *
 * @coversNothing
 */
class IntegrationMappingTest extends TestCase
{
    public function testAddsCorrectField()
    {
        $integrationType = $this->createMock(IntegrationTypeInterface::class);
        $fieldMock = $this->createMock(FieldInterface::class);
        $fieldMock->expects($this->exactly(2))
            ->method('getUid')
            ->willReturn('express-field-uid')
        ;

        $form = new Form();
        $form->addField($fieldMock);

        $mapping = new IntegrationMapping(
            $form,
            $integrationType,
            'test-resource-id',
            new ResourceFieldCollection(),
            ['test' => 'express-field-uid']
        );
        self::assertSame(
            ['test' => $fieldMock],
            $mapping->getFieldMappings()
        );
    }

    public function testConvertsToJson()
    {
        $integrationType = $this->createMock(IntegrationTypeInterface::class);
        $fieldMock = $this->createMock(FieldInterface::class);

        $fieldMock
            ->expects($this->exactly(4))
            ->method('getUid')
            ->willReturn('express-field-uid')
        ;

        $form = new Form();
        $form->addField($fieldMock);

        $mapping = new IntegrationMapping(
            $form,
            $integrationType,
            'test-resource-id',
            new ResourceFieldCollection(),
            ['test' => 'express-field-uid', 'best' => 'express-field-uid']
        );
        self::assertSame(
            '{"resourceId":"test-resource-id","fieldMap":{"test":"express-field-uid","best":"express-field-uid"}}',
            json_encode($mapping)
        );
    }
}
