<?php

namespace Solspace\ExpressForms\providers\Files;

use Solspace\ExpressForms\fields\File;

interface FileUploadInterface
{
    /**
     * Upload any files uploaded by the $field
     * And return an array of storable Asset IDs.
     *
     * @return null|array
     */
    public function upload(File $field);
}
