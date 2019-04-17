<?php

namespace Solspace\ExpressForms\providers\Security;

interface HashingInterface
{
    /**
     * @return string
     */
    public function getProjectSecretKey(): string;

    /**
     * @param string $value
     * @param string $secret
     *
     * @return string
     */
    public function hash($value, $secret): string;

    /**
     * Returns the validated data as string, or FALSE
     *
     * @param string $value
     * @param string $secret
     *
     * @return string|bool
     */
    public function deHash(string $value, string $secret);

    /**
     * Encrypts a string of data with the project secret + salt if provided
     *
     * @param string $value
     * @param string $salt
     *
     * @return string|null
     */
    public function encrypt(string $value, string $salt = null);

    /**
     * Decrypts a string of data with the project secret + salt if provided
     *
     * @param string $value
     * @param string $salt
     *
     * @return string|null
     */
    public function decrypt(string $value, string $salt = null);
}
