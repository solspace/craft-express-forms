<?php

namespace Solspace\ExpressForms\controllers;

use craft\web\Controller;
use Solspace\ExpressForms\exceptions\Integrations\ConnectionFailedException;
use Solspace\ExpressForms\ExpressForms;
use Solspace\ExpressForms\services\Integrations;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class IntegrationsController extends Controller
{
    public function actionCheckConnection(): Response
    {
        $this->requirePostRequest();
        $this->forceAjax();

        $handle = $this->post('handle');
        $type = $this->getIntegrationService()->getIntegrationByHandle($handle);
        if (!$type) {
            return $this->asJson(['success' => false, 'errors' => ['Integration not found']]);
        }

        try {
            $type->checkConnection();
            if ($type->isMarkedForUpdate()) {
                ExpressForms::getInstance()->integrations->storeConfig($type);
            }
        } catch (ConnectionFailedException $exception) {
            return $this->asJson(['success' => false, 'errors' => [$exception->getMessage()]]);
        }

        return $this->asJson(['success' => true]);
    }

    public function actionRefreshResources(): Response
    {
        $this->requirePostRequest();
        $this->forceAjax();

        $post = \GuzzleHttp\json_decode(\Craft::$app->request->getRawBody(), true);

        $handle = $post['handle'] ?? null;
        $type = $this->getIntegrationService()->getIntegrationByHandle($handle);
        if (!$type) {
            return $this->asJson(['success' => false, 'errors' => ['Integration not found']]);
        }

        try {
            $this->getIntegrationService()->fetchData($type);
        } catch (ConnectionFailedException $exception) {
            return $this->asJson(['success' => false, 'errors' => [$exception->getMessage()]]);
        }

        $metadata = $this->getIntegrationService()->getIntegrationTypeMetadata($type);

        return $this->asJson(['success' => true, 'resources' => $metadata['resources'] ?? []]);
    }

    private function forceAjax(): void
    {
        if (!\Craft::$app->request->isAjax) {
            throw new NotFoundHttpException('Page not found');
        }
    }

    private function post(string $name, mixed $value = null): mixed
    {
        return \Craft::$app->request->post($name, $value);
    }

    private function getIntegrationService(): Integrations
    {
        return ExpressForms::getInstance()->integrations;
    }
}
