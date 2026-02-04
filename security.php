<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function encrypt_value(string $plaintext): string
{
    $key = hash('sha256', APP_KEY, true);
    $iv = random_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        return '';
    }

    return base64_encode($iv . $ciphertext);
}

function decrypt_value(string $payload): string
{
    $decoded = base64_decode($payload, true);
    if ($decoded === false || strlen($decoded) < 17) {
        return '';
    }

    $key = hash('sha256', APP_KEY, true);
    $iv = substr($decoded, 0, 16);
    $ciphertext = substr($decoded, 16);
    $plaintext = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return $plaintext === false ? '' : $plaintext;
}

function set_secure_cookie(string $name, string $value, int $ttlSeconds = 0): void
{
    $expires = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
    setcookie($name, $value, [
        'expires' => $expires,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
