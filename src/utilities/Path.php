<?php

namespace Solspace\ExpressForms\utilities;

class Path
{
    /**
     * @param string $path
     *
     * @return string
     */
    public static function getAbsoluteTemplatesPath($path): string
    {
        $isAbsolute = self::isFolderAbsolute($path);

        $path = $isAbsolute ? $path : (\Craft::$app->path->getSiteTemplatesPath() . '/' . $path);

        return rtrim($path, '/');
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private static function isFolderAbsolute($path): bool
    {
        return preg_match('/^(?:\/|\\\\|\w\:\\\\).*$/', $path);
    }
}
