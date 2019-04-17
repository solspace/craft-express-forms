<?php

namespace Solspace\ExpressForms\providers\Files;

use Solspace\ExpressForms\fields\File;

interface FileUploadInterface
{
    /**
     * Upload any files uploaded by the $field
     * And return an array of storable Asset IDs
     *
     * @param File $field
     *
     * @return array|null
     */
    public function upload(File $field);
}
