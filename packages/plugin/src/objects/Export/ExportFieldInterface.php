<?php

namespace Solspace\ExpressForms\objects\Export;

interface ExportFieldInterface
{
    public function getHandle(): string;

    public function getLabel(): string;

    public function transformValue(mixed $value);
}
