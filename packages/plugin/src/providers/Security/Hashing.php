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

    public function hash(string $value, string $secret): string
    {
        return \Craft::$app->getSecurity()->hashData($value, $secret);
    }

    public function deHash(string $value, string $secret): false|string
    {
        return \Craft::$app->getSecurity()->validateData($value, $secret);
    }

    public function encrypt(string $value, string $salt = null, bool $baseEncode = true): ?string
    {
        $encrypted = \Craft::$app->getSecurity()->encryptByKey($value, $this->getProjectSecretKey().$salt);

        return $baseEncode ? base64_encode($encrypted) : $encrypted;
    }

    public function decrypt(string $value, string $salt = null, bool $baseDecode = true): string|false
    {
        $decoded = $baseDecode ? base64_decode($value) : $value;

        return \Craft::$app->getSecurity()->decryptByKey($decoded, $this->getProjectSecretKey().$salt);
    }
}
