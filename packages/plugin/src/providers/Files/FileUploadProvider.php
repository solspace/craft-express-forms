<?php

namespace Solspace\ExpressForms\providers\Files;

use craft\elements\Asset;
use craft\helpers\Assets;
use craft\web\UploadedFile;
use Solspace\ExpressForms\events\fields\FileUploadEvent;
use Solspace\ExpressForms\fields\File;
use Solspace\ExpressForms\loggers\ExpressFormsLogger;
use yii\base\Event;

class FileUploadProvider implements FileUploadInterface
{
    const EVENT_BEFORE_UPLOAD = 'beforeUpload';
    const EVENT_AFTER_UPLOAD = 'afterUpload';

    /**
     * @return null|array
     */
    public function upload(File $field)
    {
        if (!$_FILES || !isset($_FILES[$field->getHandle()]) || empty($_FILES[$field->getHandle()]['name'])) {
            return null;
        }

        $logger = ExpressFormsLogger::getInstance(ExpressFormsLogger::FILE_UPLOAD);

        if (!$field->getVolumeId()) {
            $logger->error(
                sprintf('Field "%s" does not have a valid Volume ID specified', $field->getHandle())
            );

            return null;
        }

        $assetService = \Craft::$app->assets;
        $folder = $assetService->getRootFolderByVolumeId($field->getVolumeId());

        if (!$folder) {
            $logger->error(sprintf('Folder not found by Volume ID "%d"', $field->getVolumeId()));
        }

        $uploadedFileCount = \count($_FILES[$field->getHandle()]['name']);

        $beforeUploadEvent = new FileUploadEvent($field);
        Event::trigger($this, self::EVENT_BEFORE_UPLOAD, $beforeUploadEvent);

        if (!$beforeUploadEvent->isValid) {
            return null;
        }

        $uploadedAssetIds = $errors = [];
        for ($i = 0; $i < $uploadedFileCount; ++$i) {
            $uploadedFile = UploadedFile::getInstanceByName($field->getHandle()."[{$i}]");

            if (!$uploadedFile) {
                continue;
            }

            $asset = $response = null;

            try {
                $filename = Assets::prepareAssetName($uploadedFile->name);
                $asset = new Asset();

                $asset->tempFilePath = $uploadedFile->tempName;
                $asset->filename = $filename;
                $asset->newFolderId = $folder->id;
                $asset->volumeId = $folder->volumeId;
                $asset->avoidFilenameConflicts = true;
                $asset->setScenario(Asset::SCENARIO_CREATE);

                $response = \Craft::$app->getElements()->saveElement($asset);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }

            if ($response) {
                $uploadedAssetIds[] = $asset->id;
            } elseif ($asset) {
                $errors = array_merge($errors, $asset->getErrors());
                $logger->error(
                    sprintf(
                        'Upload for "%s" failed: %s',
                        $field->getHandle(),
                        implode(', ', $errors)
                    )
                );
            }
        }

        Event::trigger($this, self::EVENT_AFTER_UPLOAD, new FileUploadEvent($field));

        return $uploadedAssetIds;
    }
}
