<?php

namespace Solspace\ExpressForms\objects\Export;

interface ExportFieldInterface
{
    /**
     * @return string
     */
    public function getHandle(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function transformValue($value);
}
