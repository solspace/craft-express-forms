<?php

namespace Solspace\ExpressForms\providers\Security;

interface HashingInterface
{
    public function getProjectSecretKey(): string;

    public function getUuid4(): string;

    /**
     * @param string $value
     * @param string $secret
     */
    public function hash($value, $secret): string;

    /**
     * Returns the validated data as string, or FALSE.
     *
     * @return bool|string
     */
    public function deHash(string $value, string $secret);

    /**
     * Encrypts a string of data with the project secret + salt if provided.
     *
     * @param string $salt
     *
     * @return null|string
     */
    public function encrypt(string $value, string $salt = null);

    /**
     * Decrypts a string of data with the project secret + salt if provided.
     *
     * @param string $salt
     *
     * @return null|string
     */
    public function decrypt(string $value, string $salt = null);
}
