<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $savePath = session_save_path();
    if ($savePath === '' || !is_dir($savePath) || !is_writable($savePath)) {
        $savePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0700, true);
        }
        session_save_path($savePath);
    }
    session_start();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
