<?php

namespace Solspace\ExpressForms\objects\Export;

interface ExportFieldInterface
{
    public function getHandle(): string;

    public function getLabel(): string;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function transformValue($value);
}
