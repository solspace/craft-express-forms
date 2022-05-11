<?php
/**
 * Express Forms for Craft.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2019-2022, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/express-forms/v2/
 *
 * @license       https://docs.solspace.com/license-agreement/
 */

namespace Solspace\ExpressForms\utilities\CodePack;

use Solspace\ExpressForms\utilities\CodePack\Components\AssetsFileComponent;
use Solspace\ExpressForms\utilities\CodePack\Components\RoutesComponent;
use Solspace\ExpressForms\utilities\CodePack\Components\TemplatesFileComponent;
use Solspace\ExpressForms\utilities\CodePack\Exceptions\CodepackException;
use Solspace\ExpressForms\utilities\CodePack\Exceptions\FileObject\FileObjectException;
use Solspace\ExpressForms\utilities\CodePack\Exceptions\Manifest\ManifestNotPresentException;
use Symfony\Component\Filesystem\Filesystem;

class CodePack
{
    public const MANIFEST_NAME = 'manifest.json';

    /** @var string */
    private $location;

    /** @var Manifest */
    private $manifest;

    /** @var TemplatesFileComponent */
    private $templates;

    /** @var AssetsFileComponent */
    private $assets;

    /** @var RoutesComponent */
    private $routes;

    /**
     * Codepack constructor.
     *
     * @param string $location
     *
     * @throws CodepackException
     * @throws ManifestNotPresentException
     */
    public function __construct($location)
    {
        $fs = new Filesystem();

        if (!$fs->exists($location)) {
            throw new CodepackException(
                sprintf(
                    "Codepack folder does not exist in '%s'",
                    $location
                )
            );
        }

        $this->location = $location;
        $this->manifest = $this->assembleManifest();
        $this->templates = $this->assembleTemplates();
        $this->assets = $this->assembleAssets();
        $this->routes = $this->assembleRoutes();
    }

    public static function getCleanPrefix(string $prefix): string
    {
        $prefix = preg_replace('/\\/+/', '/', $prefix);

        return trim($prefix, '/');
    }

    /**
     * @throws FileObjectException
     */
    public function install(string $prefix)
    {
        $prefix = self::getCleanPrefix($prefix);

        $this->templates->install($prefix);
        $this->assets->install($prefix);
        $this->routes->install($prefix);
    }

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getTemplates(): TemplatesFileComponent
    {
        return $this->templates;
    }

    public function getAssets(): AssetsFileComponent
    {
        return $this->assets;
    }

    public function getRoutes(): RoutesComponent
    {
        return $this->routes;
    }

    /**
     * Assembles a Manifest object based on the manifest file.
     */
    private function assembleManifest(): Manifest
    {
        return new Manifest($this->location.'/'.self::MANIFEST_NAME);
    }

    /**
     * Gets a TemplatesComponent object with all installable templates found.
     */
    private function assembleTemplates(): TemplatesFileComponent
    {
        return new TemplatesFileComponent($this->location);
    }

    /**
     * Gets an AssetsComponent object with all installable assets found.
     */
    private function assembleAssets(): AssetsFileComponent
    {
        return new AssetsFileComponent($this->location);
    }

    /**
     * Gets a RoutesComponent object with all installable routes.
     */
    private function assembleRoutes(): RoutesComponent
    {
        return new RoutesComponent($this->location);
    }
}
