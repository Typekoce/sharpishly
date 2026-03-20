<?php
declare(strict_types=1);

namespace App;

use Exception;

class Vault
{
    /**
     * Retrieves the master key from the secure storage area.
     */
    private static function getKey(): string
    {
        $path = __DIR__ . '/../../storage/vault/master.key';
        if (!file_exists($path)) {
            throw new Exception("Security Breach: Master Key missing.");
        }
        return base64_decode(file_get_contents($path));
    }

    /**
     * Encrypts sensitive data (API Keys, OAuth Tokens)
     */
    public static function encrypt(string $data): string
    {
        $key = self::getKey();
        $iv = openssl_random_pseudo_bytes(16);
        $enc = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($enc . '::' . $iv);
    }

    /**
     * Decrypts data for use in Service calls
     */
    public static function decrypt(string $data): ?string
    {
        $key = self::getKey();
        $parts = explode('::', base64_decode($data), 2);
        
        if (count($parts) !== 2) return null;
        
        list($encryptedData, $iv) = $parts;
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }
}