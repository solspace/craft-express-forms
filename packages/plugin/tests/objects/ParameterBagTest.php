<?php

namespace Solspace\Tests\ExpressForms\objects;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\objects\ParameterBag;

/**
 * @internal
 * @coversNothing
 */
class ParameterBagTest extends TestCase
{
    public function testCreateEmptyParameterBag()
    {
        $bag = new ParameterBag();

        self::assertCount(0, $bag);
    }

    public function testParameterBagWithItems()
    {
        $bag = new ParameterBag(['test' => 'one', 'two' => 'value']);

        $expected = ['test' => 'one', 'two' => 'value'];

        self::assertCount(2, $bag);
        self::assertSame('one', $bag['test']);
        self::assertSame('value', $bag['two']);
        self::assertSame($expected, $bag->toArray());
    }

    public function testMergeItems()
    {
        $bag = new ParameterBag(['test' => 'one', 'two' => 'value']);
        $bag->merge(['two' => 'different', 'pillar' => 'of community']);

        $expected = ['test' => 'one', 'two' => 'different', 'pillar' => 'of community'];

        self::assertSame($expected, $bag->toArray());
    }

    public function testGetValue()
    {
        $bag = new ParameterBag(['test' => 'one', 'two' => 'value']);

        self::assertSame('value', $bag->get('two'));
    }

    public function testGetNonExistingReturnsNullByDefault()
    {
        $bag = new ParameterBag();

        self::assertNull($bag->get('non-existent'));
    }

    public function testGetReturnsSetDefaultValue()
    {
        $bag = new ParameterBag();

        self::assertSame('typhoon', $bag->get('non-existent', 'typhoon'));
    }

    public function testHasReturnsTrueForExisting()
    {
        $bag = new ParameterBag(['some' => 'value']);

        self::assertTrue($bag->has('some'));
    }

    public function testHasReturnsFalseForNonExisting()
    {
        $bag = new ParameterBag(['some' => 'value']);

        self::assertFalse($bag->has('non-existing'));
    }

    public function testRemoveItem()
    {
        $bag = new ParameterBag(['test' => 'one', 'two' => 'value', 'three' => 'another']);

        $expected = ['test' => 'one', 'three' => 'another'];

        self::assertSame($expected, $bag->remove('two')->toArray());
    }

    public function testSetDifferentValues()
    {
        $bag = new ParameterBag(['test' => 'one', 'two' => 'value', 'three' => 'another']);
        $bag->set(['different' => 'values']);

        self::assertSame(['different' => 'values'], $bag->toArray());
    }
}
