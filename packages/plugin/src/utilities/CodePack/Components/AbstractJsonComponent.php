<?php
/**
 * Express Forms for Craft.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2019-2024, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/express-forms/v2/
 *
 * @license       https://docs.solspace.com/license-agreement/
 */

namespace Solspace\ExpressForms\utilities\CodePack\Components;

use Solspace\ExpressForms\utilities\CodePack\Exceptions\CodepackException;

abstract class AbstractJsonComponent implements ComponentInterface
{
    /** @var string */
    protected $fileName;

    /** @var mixed */
    private $jsonData;

    /**
     * ComponentInterface constructor.
     *
     * @throws CodepackException
     */
    final public function __construct(string $location)
    {
        $this->setProperties();

        if (null === $this->fileName) {
            throw new CodepackException('JSON file name not specified');
        }

        $this->parseJson($location);
    }

    /**
     * Returns the parsed JSON data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->jsonData;
    }

    /**
     * Calls the installation of this component.
     *
     * @param string $prefix
     */
    abstract public function install(string $prefix = null);

    /**
     * This is the method that sets all vital properties
     * ::$fileName.
     */
    abstract protected function setProperties();

    /**
     * @throws CodepackException
     */
    private function parseJson(string $location): bool
    {
        $jsonFile = $location.'/'.$this->fileName;
        if (!file_exists($jsonFile)) {
            return false;
        }

        $content = file_get_contents($jsonFile);
        $parsedData = json_decode($content);

        if (json_last_error()) {
            throw new CodepackException('Codepack JSON component: '.json_last_error_msg());
        }

        if ($parsedData) {
            $this->jsonData = $parsedData;
        }

        return true;
    }
}
