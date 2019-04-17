<?php

namespace Solspace\ExpressForms\models;

use craft\base\Model;
use Solspace\ExpressForms\services\Honeypot;

class Settings extends Model
{
    const DEFAULT_CATEGORY_NAME = 'Default';

    public $name;
    public $enhancedUI = true;
    public $showErrorLogBanner = true;

    public $honeypotEnabled   = false;
    public $honeypotBehaviour = Honeypot::BEHAVIOUR_SIMULATE_SUCCESS;
    public $honeypotInputName = 'form_handler';

    public $recaptchaEnabled = false;
    public $recaptchaLoadScript = true;
    public $recaptchaSiteKey;
    public $recaptchaSecretKey;

    public $emailNotificationsDirectoryPath;

    /**
     * @return string|null
     */
    public function getEmailNotificationsPath()
    {
        if (!$this->emailNotificationsDirectoryPath) {
            return null;
        }

        return $this->getAbsolutePath($this->emailNotificationsDirectoryPath);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getAbsolutePath($path): string
    {
        $isAbsolute = $this->isFolderAbsolute($path);

        $path = $isAbsolute ? $path : (\Craft::$app->path->getSiteTemplatesPath() . '/' . $path);

        return rtrim($path, '/');
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isFolderAbsolute($path): bool
    {
        return preg_match('/^(?:\/|\\\\|\w\:\\\\).*$/', $path);
    }
}
