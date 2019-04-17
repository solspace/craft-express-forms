<?php

namespace Solspace\ExpressForms\events\integrations;

use craft\events\CancelableEvent;
use Solspace\ExpressForms\integrations\IntegrationInterface;

class IntegrationBuildFromArrayEvent extends CancelableEvent
{
    /** @var IntegrationInterface */
    public $integration;

    /** @var array */
    public $data;
}
