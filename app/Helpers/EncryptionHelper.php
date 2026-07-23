<?php

namespace App\Helpers;

class EncryptionHelper
{
    // AES-GCM constants
    private const CIPHER    = 'aes-256-gcm';
    private const IV_LENGTH = 12; // 96-bit IV — GCM standard
    private const TAG_LENGTH = 16; // 128-bit authentication tag

    /**
     * Derive a 32-byte binary key from the configured key string.
     * Using SHA-256 so the raw string length doesn't matter.
     */
    private static function deriveKey(string $keyString): string
    {
        return hash('sha256', $keyString, true);
    }

    /**
     * Encrypt data using AES-256-GCM (authenticated encryption).
     *
     * Output format: base64( IV[12] + TAG[16] + Ciphertext )
     *
     * @param  mixed  $data  Array or string to encrypt.
     * @return mixed         Base64 string on success, or original $data if no key is configured.
     */
    public static function encrypt($data)
    {
        $keyString = config('app.api_encryption_key');
        if (!$keyString) {
            return $data;
        }

        $key       = self::deriveKey($keyString);
        $plaintext = is_string($data) ? $data : json_encode($data);

        // Generate a cryptographically secure 12-byte IV (GCM standard)
        $iv  = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,          // GCM outputs the authentication tag here
            '',            // no AAD
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            return $data;
        }

        // Wire format: IV (12 B) | TAG (16 B) | Ciphertext
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt an AES-256-GCM encrypted payload.
     *
     * Expected input format: base64( IV[12] + TAG[16] + Ciphertext )
     *
     * Returns the decrypted value as an array (if JSON) or raw string.
     * Returns null when decryption or authentication fails.
     *
     * @param  string  $payload  Base64-encoded ciphertext.
     * @return mixed
     */
    public static function decrypt(string $payload)
    {
        $keyString = config('app.api_encryption_key');
        if (!$keyString) {
            return $payload;
        }

        $key     = self::deriveKey($keyString);
        $decoded = base64_decode($payload, true);

        // Minimum length: IV(12) + TAG(16) + at least 1 byte of ciphertext
        if ($decoded === false || strlen($decoded) < self::IV_LENGTH + self::TAG_LENGTH + 1) {
            return null;
        }

        // Unpack: IV | TAG | Ciphertext
        $iv         = substr($decoded, 0, self::IV_LENGTH);
        $tag        = substr($decoded, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($decoded, self::IV_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag   // GCM verifies the tag here — returns false if tampered
        );

        if ($plaintext === false) {
            // Authentication failed (tampered ciphertext or wrong key)
            return null;
        }

        $decoded = json_decode($plaintext, true);
        return $decoded !== null ? $decoded : $plaintext;
    }
}
