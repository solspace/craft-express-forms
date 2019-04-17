<?php

namespace Solspace\ExpressForms\providers\Files;

use craft\helpers\Assets;

class FileTypeProvider implements FileTypeProviderInterface
{
    /**
     * Returns an array of all file kinds
     * [type => [ext, ext, ..]
     * I.e. ["image" => ["gif", "png", "jpg", "jpeg", ..]]
     *
     * @return array
     */
    public function getFileKinds(): array
    {
        $fileKinds = Assets::getFileKinds();

        $returnArray = [];
        foreach ($fileKinds as $kind => $extensions) {
            $returnArray[$kind] = $extensions['extensions'];
        }

        return $returnArray;
    }

    /**
     * @param array $fileKinds
     *
     * @return array
     */
    public function getValidExtensionsForFileKinds(array $fileKinds): array
    {
        $allFileKinds = $this->getFileKinds();

        $allowedExtensions = [];
        foreach ($fileKinds as $kind) {
            if (array_key_exists($kind, $allFileKinds)) {
                $allowedExtensions = array_merge($allowedExtensions, $allFileKinds[$kind]);
            }
        }

        return $allowedExtensions;
    }
}
