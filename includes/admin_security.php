<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function clientIpAddress(): ?string
{
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    return $ip !== '' ? $ip : null;
}

function clientUserAgent(): ?string
{
    $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    return $ua !== '' ? $ua : null;
}

function logAdminLoginAttempt(string $email, bool $success, ?int $adminId = null): void
{
    try {
        $pdo = getDatabaseConnection();
        $pdo->prepare(
            'INSERT INTO admin_login_logs (admin_id, email, success, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $adminId,
            substr(strtolower(trim($email)), 0, 190),
            $success ? 1 : 0,
            clientIpAddress(),
            clientUserAgent(),
        ]);
    } catch (Throwable $e) {
        error_log('Login log failed: ' . $e->getMessage());
    }
}

function upsertAdminSession(int $adminId): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    try {
        $pdo = getDatabaseConnection();
        $sid = session_id();
        $pdo->prepare(
            'INSERT INTO admin_sessions (admin_id, session_id, ip_address, user_agent, last_seen_at)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                admin_id = VALUES(admin_id),
                ip_address = VALUES(ip_address),
                user_agent = VALUES(user_agent),
                last_seen_at = NOW(),
                revoked_at = NULL'
        )->execute([$adminId, $sid, clientIpAddress(), clientUserAgent()]);
    } catch (Throwable $e) {
        error_log('Session upsert failed: ' . $e->getMessage());
    }
}

function touchAdminSession(): void
{
    if (empty($_SESSION['admin_id']) || session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }
    try {
        $pdo = getDatabaseConnection();
        $pdo->prepare(
            'UPDATE admin_sessions SET last_seen_at = NOW() WHERE session_id = ? AND revoked_at IS NULL'
        )->execute([session_id()]);
    } catch (Throwable $e) {
        // ignore
    }
}

function revokeAdminSession(string $sessionId): void
{
    $pdo = getDatabaseConnection();
    $pdo->prepare('UPDATE admin_sessions SET revoked_at = NOW() WHERE session_id = ?')->execute([$sessionId]);
}

function recordPropertyEditHistory(int $propertyId, string $summary, ?array $before = null, ?array $after = null): void
{
    try {
        $pdo = getDatabaseConnection();
        $pdo->prepare(
            'INSERT INTO property_edit_history (property_id, admin_id, change_summary, before_json, after_json)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $propertyId,
            !empty($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null,
            substr($summary, 0, 255),
            $before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
            $after !== null ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable $e) {
        error_log('Property edit history failed: ' . $e->getMessage());
    }
}

function recordPropertyPriceChange(
    int $propertyId,
    ?float $oldMin,
    ?float $newMin,
    ?float $oldMax,
    ?float $newMax
): void {
    if ($oldMin === $newMin && $oldMax === $newMax) {
        return;
    }
    try {
        $pdo = getDatabaseConnection();
        $pdo->prepare(
            'INSERT INTO property_price_history
                (property_id, old_min_price, new_min_price, old_max_price, new_max_price, changed_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $propertyId,
            $oldMin,
            $newMin,
            $oldMax,
            $newMax,
            !empty($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null,
        ]);
    } catch (Throwable $e) {
        error_log('Price history failed: ' . $e->getMessage());
    }
}
