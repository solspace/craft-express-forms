<?php

namespace Solspace\Tests\ExpressForms\serializers;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\fields\Text;
use Solspace\ExpressForms\serializers\FieldSerializer;

/**
 * @internal
 * @coversNothing
 */
class FieldSerializerTest extends TestCase
{
    public function testToArray()
    {
        $field = new Text();
        $field->uid = 'test';
        $field->name = 'Test Field';

        $serializer = new FieldSerializer();
        $result = $serializer->toArray($field);

        $expectedResult = [
            'id' => null,
            'name' => 'Test Field',
            'handle' => null,
            'type' => 'text',
            'uid' => 'test',
            'required' => false,
        ];

        self::assertEquals($expectedResult, $result);
    }

    public function testToJson()
    {
        $field = new Text();
        $field->uid = 'test';
        $field->name = 'test field';
        $field->required = true;

        $serializer = new FieldSerializer();
        $result = $serializer->toJson($field);

        $expectedResult = '{"id":null,"uid":"test","name":"test field","handle":null,"type":"text","required":true}';

        self::assertEquals($expectedResult, $result);
    }
}
