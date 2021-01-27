<?php

namespace Solspace\ExpressForms\utilities;

class StringManipulation
{
    /**
     * Takes a string, and separates all of the items that are
     * separated by space, newline, comma or semicolon
     * and returns them as array.
     */
    public static function stringItemsToArray(string $string): array
    {
        $string = preg_replace('/(,|;|\n)+/', ' ', $string);
        $string = preg_replace('/ +/', ' ', $string);

        $items = explode(' ', $string);
        $items = array_unique($items);

        return array_filter($items);
    }
}
