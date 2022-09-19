<?php

namespace Solspace\Tests\ExpressForms\serializers;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\models\Form;
use Solspace\ExpressForms\serializers\FieldSerializer;
use Solspace\ExpressForms\serializers\FormSerializer;

/**
 * @internal
 *
 * @coversNothing
 */
class FormSerializerTest extends TestCase
{
    /** @var FormSerializer */
    private $formSerializer;

    /** @var FieldSerializer|MockObject */
    private $fieldSerializerMock;

    protected function setUp(): void
    {
        $this->fieldSerializerMock = $this->createMock(FieldSerializer::class);

        $this->formSerializer = new FormSerializer($this->fieldSerializerMock);
    }

    public function testToArray()
    {
        $fieldMock = $this->createMock(Text::class);

        $form = new Form();
        $form->setName('Test form')
            ->setColor('hash')
            ->addField($fieldMock)
            ->addField($fieldMock)
        ;

        $this->fieldSerializerMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn([])
        ;

        $result = $this->formSerializer->toArray($form);

        $expectedResult = [
            'id' => null,
            'uuid' => $form->getUuid(),
            'name' => 'Test form',
            'handle' => null,
            'description' => null,
            'color' => 'hash',
            'submissionTitle' => '{{ dateCreated|date("Y-m-d H:i:s") }}',
            'saveSubmissions' => true,
            'adminNotification' => null,
            'adminEmails' => null,
            'submitterNotification' => null,
            'submitterEmailField' => null,
            'fields' => [[]],
            'integrations' => [],
            'spamCount' => 0,
        ];

        self::assertEquals($expectedResult, $result);
    }

    public function testToJson()
    {
        $fieldMock = $this->createMock(Text::class);

        $form = new Form();
        $form->setName('Test form')
            ->setColor('hash')
            ->addField($fieldMock)
            ->addField($fieldMock)
        ;

        $this->fieldSerializerMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn([])
        ;

        $result = $this->formSerializer->toJson($form);

        $uuid = $form->getUuid();
        self::assertEquals(
            '{"id":null,"uuid":"'.$uuid.'","name":"Test form","handle":null,"description":null,"color":"hash","submissionTitle":"{{ dateCreated|date(\"Y-m-d H:i:s\") }}","saveSubmissions":true,"adminNotification":null,"adminEmails":null,"submitterNotification":null,"submitterEmailField":null,"fields":[[]],"integrations":[],"spamCount":0}',
            $result
        );
    }
}
