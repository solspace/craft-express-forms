<?php

namespace Solspace\ExpressForms\providers\View;

interface RequestProviderInterface
{
    /**
     * @return bool
     */
    public function isCpRequest(): bool;

    /**
     * @return bool
     */
    public function isAjaxRequest(): bool;

    /**
     * @return string
     */
    public function getRemoteIP(): string;

    /**
     * @param string $url
     * @param int    $statusCode
     */
    public function redirect(string $url, int $statusCode = 302);

    /**
     * @param int $statusCode
     */
    public function redirectToReferrer(int $statusCode = 302);

    /**
     * @param string $name
     * @param null   $defaultValue
     *
     * @return mixed
     */
    public function post(string $name, $defaultValue = null);
}
