<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

function adminUrl(string $path = ''): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '' || $path === 'index.php' || $path === 'index') {
        return webPath('admin');
    }
    return webPath('admin/' . $path);
}

function adminRedirect(string $path, array $query = []): never
{
    $url = adminUrl($path);
    if ($query !== []) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }
    header('Location: ' . $url, true, 303);
    exit;
}

function adminFlash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function adminTakeFlash(): ?array
{
    if (empty($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
        return null;
    }
    $flash = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
    return $flash;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'property';
}

function uniquePropertySlug(PDO $pdo, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $candidate = $slug;
    $i = 2;
    $stmt = $pdo->prepare('SELECT id FROM properties WHERE slug = ? LIMIT 1');

    while (true) {
        $stmt->execute([$candidate]);
        $found = $stmt->fetchColumn();
        if ($found === false || ($ignoreId !== null && (int) $found === $ignoreId)) {
            return $candidate;
        }
        $candidate = $slug . '-' . $i;
        $i++;
    }
}

function logAdminActivity(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    if (empty($_SESSION['admin_id'])) {
        return;
    }

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO admin_activity_logs (admin_id, action, entity_type, entity_id, details)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int) $_SESSION['admin_id'],
            $action,
            $entityType,
            $entityId,
            $details,
        ]);
    } catch (Throwable $e) {
        error_log('Admin activity log failed: ' . $e->getMessage());
    }
}

function parseMoneyToRupees(?string $raw): ?float
{
    if ($raw === null) {
        return null;
    }
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }

    $lower = strtolower($raw);
    $number = (float) preg_replace('/[^0-9.]/', '', $raw);

    if (str_contains($lower, 'cr')) {
        return $number * 10000000;
    }
    if (str_contains($lower, 'l') || str_contains($lower, 'lac') || str_contains($lower, 'lakh')) {
        return $number * 100000;
    }

    return $number;
}

function formatRupeesInput(?float $amount): string
{
    if ($amount === null || $amount <= 0) {
        return '';
    }
    return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
}
