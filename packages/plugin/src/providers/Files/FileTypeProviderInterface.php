<?php

namespace Solspace\ExpressForms\providers\Files;

interface FileTypeProviderInterface
{
    /**
     * Returns an array of all file kinds
     * [type => [ext, ext, ..]
     * I.e. ["image" => ["gif", "png", "jpg", "jpeg", ..]].
     */
    public function getFileKinds(): array;

    /**
     * Returns an array of all extensions for provided file kinds.
     */
    public function getValidExtensionsForFileKinds(array $fileKinds): array;
}
