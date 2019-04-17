<?php

namespace Solspace\ExpressForms\services;

class Honeypot extends BaseService
{
    const DEFAULT_NAME = 'form_handler';

    const BEHAVIOUR_SIMULATE_SUCCESS = 'simulate_success';
    const BEHAVIOUR_SHOW_ERRORS      = 'display_errors';
    const BEHAVIOUR_RELOAD_FORM      = 'reload_form';
}
