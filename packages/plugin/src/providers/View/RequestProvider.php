<?php

namespace Solspace\ExpressForms\providers\View;

use Craft;
use craft\helpers\UrlHelper;

class RequestProvider implements RequestProviderInterface
{
    public function isCpRequest(): bool
    {
        return Craft::$app->getRequest()->isCpRequest;
    }

    public function isAjaxRequest(): bool
    {
        return Craft::$app->getRequest()->isAjax;
    }

    public function getRemoteIP(): string
    {
        return Craft::$app->getRequest()->getRemoteIP();
    }

    /**
     * @return \craft\web\Response|\yii\console\Response
     */
    public function redirect(string $url, int $statusCode = 302)
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::url($url), $statusCode);
    }

    /**
     * @return \craft\web\Response|\yii\console\Response
     */
    public function redirectToReferrer(int $statusCode = 302)
    {
        $url = $_SERVER['HTTP_REFERER'] ?? '';

        return Craft::$app->getResponse()->redirect(UrlHelper::url($url), $statusCode);
    }

    /**
     * @param null $defaultValue
     *
     * @return array|mixed
     */
    public function post(string $name, $defaultValue = null)
    {
        return Craft::$app->getRequest()->post($name, $defaultValue);
    }
}
