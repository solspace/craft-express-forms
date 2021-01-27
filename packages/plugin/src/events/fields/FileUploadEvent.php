<?php

namespace Solspace\ExpressForms\events\fields;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\fields\File;

class FileUploadEvent extends CancelableEvent
{
    /** @var File */
    private $field;

    /**
     * FileUploadEvent constructor.
     */
    public function __construct(File $field)
    {
        $this->field = $field;
    }

    public function getField(): File
    {
        return $this->field;
    }
}
