<?php

namespace Solspace\ExpressForms\providers\Files;

interface FileTypeProviderInterface
{
    /**
     * Returns an array of all file kinds
     * [type => [ext, ext, ..]
     * I.e. ["image" => ["gif", "png", "jpg", "jpeg", ..]]
     *
     * @return array
     */
    public function getFileKinds(): array;

    /**
     * Returns an array of all extensions for provided file kinds
     *
     * @param array $fileKinds
     *
     * @return array
     */
    public function getValidExtensionsForFileKinds(array $fileKinds): array;
}
