<?php

namespace Solspace\ExpressForms\models;

use craft\base\Model;
use Solspace\ExpressForms\services\Honeypot;
use Solspace\ExpressForms\utilities\Path;

class Settings extends Model
{
    const DEFAULT_CATEGORY_NAME = 'Default';

    public $name;
    public $enhancedUI = true;
    public $showErrorLogBanner = true;

    public $honeypotEnabled = false;
    public $honeypotBehaviour = Honeypot::BEHAVIOUR_SIMULATE_SUCCESS;
    public $honeypotInputName = 'form_handler';

    public $recaptchaEnabled = false;
    public $recaptchaLoadScript = true;
    public $recaptchaSiteKey;
    public $recaptchaSecretKey;
    public $recaptchaTheme = 'light';

    public $emailNotificationsDirectoryPath;

    public $duplicatePreventionEnabled = true;

    /**
     * @return null|string
     */
    public function getEmailNotificationsPath()
    {
        if (!$this->emailNotificationsDirectoryPath) {
            return null;
        }

        return Path::getAbsoluteTemplatesPath($this->emailNotificationsDirectoryPath);
    }
}
