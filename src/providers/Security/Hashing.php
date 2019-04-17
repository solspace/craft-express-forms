<?php

namespace Solspace\ExpressForms\providers\Security;

class Hashing implements HashingInterface
{
    /**
     * @return string
     */
    public function getProjectSecretKey(): string
    {
        return \Craft::$app->getConfig()->getGeneral()->securityKey;
    }

    /**
     * @param string $value
     * @param string $secret
     *
     * @return string
     */
    public function hash($value, $secret): string
    {
        return \Craft::$app->getSecurity()->hashData($value, $secret);
    }

    /**
     * @param string $value
     * @param string $secret
     *
     * @return string|bool
     */
    public function deHash(string $value, string $secret)
    {
        return \Craft::$app->getSecurity()->validateData($value, $secret);
    }

    /**
     * @param string      $value
     * @param string|null $salt
     * @param bool        $baseEncode
     *
     * @return string|null
     */
    public function encrypt(string $value, string $salt = null, bool $baseEncode = true)
    {
        $encrypted = \Craft::$app->getSecurity()->encryptByKey($value, $this->getProjectSecretKey() . $salt);

        return $baseEncode ? base64_encode($encrypted) : $encrypted;
    }

    /**
     * @param string      $value
     * @param string|null $salt
     * @param bool        $baseDecode
     *
     * @return bool|string
     */
    public function decrypt(string $value, string $salt = null, bool $baseDecode = true)
    {
        $decoded = $baseDecode ? base64_decode($value) : $value;

        return \Craft::$app->getSecurity()->decryptByKey($decoded, $this->getProjectSecretKey() . $salt);
    }
}
