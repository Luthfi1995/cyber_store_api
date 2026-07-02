<?php

namespace App\Helpers;

class EncryptionHelper
{
    /**
     * Encrypt data using AES-256-CBC
     * Output format: base64(IV + Ciphertext)
     */
    public static function encrypt($data)
    {
        $key = config('app.api_encryption_key');
        if (!$key) {
            return $data;
        }

        $plaintext = is_string($data) ? $data : json_encode($data);
        
        // Generate random 16-byte IV
        $iv = openssl_random_pseudo_bytes(16);
        
        // Encrypt using AES-256-CBC
        $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        // Combine IV and Ciphertext, then base64 encode
        return base64_encode($iv . $ciphertext);
    }

    /**
     * Decrypt data encrypted using AES-256-CBC
     * Input format: base64(IV + Ciphertext)
     */
    public static function decrypt($payload)
    {
        $key = config('app.api_encryption_key');
        if (!$key) {
            return $payload;
        }

        $decoded = base64_decode($payload);
        if (strlen($decoded) < 17) {
            return null;
        }

        // Extract IV (first 16 bytes) and Ciphertext
        $iv = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);
        
        // Decrypt
        $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        if ($plaintext === false) {
            return null;
        }

        $decodedJson = json_decode($plaintext, true);
        return $decodedJson !== null ? $decodedJson : $plaintext;
    }
}
