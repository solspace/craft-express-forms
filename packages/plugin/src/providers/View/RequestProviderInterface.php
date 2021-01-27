<?php

namespace Solspace\ExpressForms\providers\View;

interface RequestProviderInterface
{
    public function isCpRequest(): bool;

    public function isAjaxRequest(): bool;

    public function getRemoteIP(): string;

    public function redirect(string $url, int $statusCode = 302);

    public function redirectToReferrer(int $statusCode = 302);

    /**
     * @param null $defaultValue
     *
     * @return mixed
     */
    public function post(string $name, $defaultValue = null);
}
