<?php

namespace Solspace\ExpressForms\models;

use craft\base\Model;
use Solspace\ExpressForms\services\Honeypot;
use Solspace\ExpressForms\utilities\Path;

class Settings extends Model
{
    public const DEFAULT_CATEGORY_NAME = 'Default';

    public ?string $name = null;
    public bool $enhancedUI = true;
    public bool $showErrorLogBanner = true;

    public bool $honeypotEnabled = false;
    public string $honeypotBehaviour = Honeypot::BEHAVIOUR_SIMULATE_SUCCESS;
    public string $honeypotInputName = 'form_handler';

    public bool $recaptchaEnabled = false;
    public bool $recaptchaLoadScript = true;
    public ?string $recaptchaSiteKey = null;
    public ?string $recaptchaSecretKey = null;
    public string $recaptchaTheme = 'light';

    public ?string $emailNotificationsDirectoryPath = null;

    public bool $duplicatePreventionEnabled = true;

    public function getEmailNotificationsPath(): ?string
    {
        if (!$this->emailNotificationsDirectoryPath) {
            return null;
        }

        return Path::getAbsoluteTemplatesPath($this->emailNotificationsDirectoryPath);
    }
}
