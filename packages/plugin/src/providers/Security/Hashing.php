<?php

namespace Solspace\ExpressForms\providers\Security;

use craft\helpers\StringHelper;

class Hashing implements HashingInterface
{
    public function getProjectSecretKey(): string
    {
        return \Craft::$app->getConfig()->getGeneral()->securityKey;
    }

    public function getUuid4(): string
    {
        return StringHelper::UUID();
    }

    /**
     * @param string $value
     * @param string $secret
     */
    public function hash($value, $secret): string
    {
        return \Craft::$app->getSecurity()->hashData($value, $secret);
    }

    /**
     * @return bool|string
     */
    public function deHash(string $value, string $secret)
    {
        return \Craft::$app->getSecurity()->validateData($value, $secret);
    }

    /**
     * @return null|string
     */
    public function encrypt(string $value, string $salt = null, bool $baseEncode = true)
    {
        $encrypted = \Craft::$app->getSecurity()->encryptByKey($value, $this->getProjectSecretKey().$salt);

        return $baseEncode ? base64_encode($encrypted) : $encrypted;
    }

    /**
     * @return bool|string
     */
    public function decrypt(string $value, string $salt = null, bool $baseDecode = true)
    {
        $decoded = $baseDecode ? base64_decode($value) : $value;

        return \Craft::$app->getSecurity()->decryptByKey($decoded, $this->getProjectSecretKey().$salt);
    }
}
