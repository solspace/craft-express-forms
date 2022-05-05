<?php

namespace Solspace\ExpressForms\providers\Security;

interface HashingInterface
{
    public function getProjectSecretKey(): string;

    public function getUuid4(): string;

    public function hash(string $value, string $secret): string;

    /**
     * Returns the validated data as string, or FALSE.
     */
    public function deHash(string $value, string $secret): false|string;

    /**
     * Encrypts a string of data with the project secret + salt if provided.
     */
    public function encrypt(string $value, string $salt = null): ?string;

    /**
     * Decrypts a string of data with the project secret + salt if provided.
     */
    public function decrypt(string $value, string $salt = null): false|string;
}
