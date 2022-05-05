<?php

namespace Solspace\ExpressForms\services;

class Honeypot extends BaseService
{
    public const DEFAULT_NAME = 'form_handler';

    public const BEHAVIOUR_SIMULATE_SUCCESS = 'simulate_success';
    public const BEHAVIOUR_SHOW_ERRORS = 'display_errors';
    public const BEHAVIOUR_RELOAD_FORM = 'reload_form';
}
