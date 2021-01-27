<?php

namespace Solspace\ExpressForms\events\fields;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\fields\FieldInterface;

class FieldBuildFromArrayEvent extends CancelableEvent
{
    /** @var FieldInterface */
    public $field;

    /** @var array */
    public $data;
}
