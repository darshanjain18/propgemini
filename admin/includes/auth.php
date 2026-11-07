<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/admin_security.php';

function adminUser(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }

    $cached = true;
    $pdo = getDatabaseConnection();

    // Block revoked sessions.
    try {
        $sessionCheck = $pdo->prepare(
            'SELECT revoked_at FROM admin_sessions WHERE session_id = ? LIMIT 1'
        );
        $sessionCheck->execute([session_id()]);
        $revoked = $sessionCheck->fetchColumn();
        if ($revoked !== false && $revoked !== null) {
            unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
            $user = null;
            return null;
        }
    } catch (Throwable $e) {
        // Table may not exist yet during first boot.
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, email, role, is_active FROM admins WHERE id = ? LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $row = $stmt->fetch();

    if ($row === false || !(int) $row['is_active']) {
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
        $user = null;
        return null;
    }

    $user = $row;
    touchAdminSession();
    return $user;
}

function requireAdmin(): array
{
    $user = adminUser();
    if ($user === null) {
        adminRedirect('index.php', ['next' => $_SERVER['REQUEST_URI'] ?? '']);
    }
    return $user;
}

function attemptAdminLogin(string $email, string $password): bool
{
    $pdo = getDatabaseConnection();
    $normalized = strtolower(trim($email));
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, is_active FROM admins WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$normalized]);
    $row = $stmt->fetch();

    if ($row === false || !(int) $row['is_active']) {
        logAdminLoginAttempt($normalized, false, null);
        return false;
    }

    if (!password_verify($password, (string) $row['password'])) {
        logAdminLoginAttempt($normalized, false, (int) $row['id']);
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $row['id'];
    $_SESSION['admin_name'] = (string) $row['name'];
    $_SESSION['admin_email'] = (string) $row['email'];
    logAdminLoginAttempt($normalized, true, (int) $row['id']);
    upsertAdminSession((int) $row['id']);
    logAdminActivity('login');

    return true;
}

function adminLogout(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        try {
            revokeAdminSession(session_id());
        } catch (Throwable $e) {
            // ignore
        }
    }
    logAdminActivity('logout');
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
    session_regenerate_id(true);
}
