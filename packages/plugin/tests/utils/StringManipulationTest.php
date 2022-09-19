<?php

namespace Solspace\Tests\ExpressForms\utils;

use PHPUnit\Framework\TestCase;
use Solspace\ExpressForms\utilities\StringManipulation;

/**
 * @internal
 *
 * @coversNothing
 */
class StringManipulationTest extends TestCase
{
    public function testStringItemsToArrayFromEmptyString()
    {
        $items = StringManipulation::stringItemsToArray('');

        self::assertEmpty($items);
    }

    public function testStringItemsToArrayFromSpaceSeparated()
    {
        $items = StringManipulation::stringItemsToArray('one two three   four  five');

        self::assertCount(5, $items, json_encode($items));
        self::assertContains('one', $items);
        self::assertContains('two', $items);
        self::assertContains('three', $items);
        self::assertContains('four', $items);
        self::assertContains('five', $items);
    }

    public function testStringItemsToArrayFromNewline()
    {
        $items = StringManipulation::stringItemsToArray("one\n two\nthree\n\n\nfour");

        self::assertCount(4, $items, json_encode($items));
        self::assertContains('one', $items);
        self::assertContains('two', $items);
        self::assertContains('three', $items);
        self::assertContains('four', $items);
    }

    public function testStringItemsFromArrayFromCommaSeparated()
    {
        $items = StringManipulation::stringItemsToArray('one, two,three,  four');

        self::assertCount(4, $items, json_encode($items));
        self::assertContains('one', $items);
        self::assertContains('two', $items);
        self::assertContains('three', $items);
        self::assertContains('four', $items);
    }

    public function testStringItemsFromArrayFromSemicolonSeparated()
    {
        $items = StringManipulation::stringItemsToArray('one; two;three;  four');

        self::assertCount(4, $items, json_encode($items));
        self::assertContains('one', $items);
        self::assertContains('two', $items);
        self::assertContains('three', $items);
        self::assertContains('four', $items);
    }

    public function testStringItemsFromArrayFromVariousSeparationTypes()
    {
        $items = StringManipulation::stringItemsToArray("one; two\nthree,  four five");

        self::assertCount(5, $items, json_encode($items));
        self::assertContains('one', $items);
        self::assertContains('two', $items);
        self::assertContains('three', $items);
        self::assertContains('four', $items);
        self::assertContains('five', $items);
    }
}
