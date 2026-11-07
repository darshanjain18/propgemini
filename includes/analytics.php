<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function analyticsClientIp(): ?string
{
    $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    return $ip !== '' ? $ip : null;
}

function recordPropertyView(int $propertyId): void
{
    if ($propertyId <= 0) {
        return;
    }

    try {
        $pdo = getDatabaseConnection();
        $pdo->prepare(
            'INSERT INTO property_views (property_id, viewer_ip) VALUES (?, ?)'
        )->execute([$propertyId, analyticsClientIp()]);
    } catch (Throwable $e) {
        error_log('Property view tracking failed: ' . $e->getMessage());
    }
}

function recordSiteTraffic(?string $path = null, ?string $pageTitle = null, ?int $propertyId = null): void
{
    $path = $path ?? (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = strtok($path, '?') ?: '/';
    $path = substr($path, 0, 255);
    if ($path === '') {
        $path = '/';
    }

    // Skip admin / asset noise.
    if (preg_match('#/(admin|public|vendor|actions/captcha)#i', $path) === 1) {
        return;
    }

    try {
        $pdo = getDatabaseConnection();
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $ref = substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 500);
        $pdo->prepare(
            'INSERT INTO site_traffic (path, page_title, property_id, viewer_ip, user_agent, referrer)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $path,
            $pageTitle !== null ? substr($pageTitle, 0, 255) : null,
            $propertyId,
            analyticsClientIp(),
            $ua !== '' ? $ua : null,
            $ref !== '' ? $ref : null,
        ]);
    } catch (Throwable $e) {
        error_log('Site traffic tracking failed: ' . $e->getMessage());
    }
}

function recordWhatsAppClick(string $sourceLabel = 'site', ?int $propertyId = null): void
{
    try {
        $pdo = getDatabaseConnection();
        $ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $pdo->prepare(
            'INSERT INTO whatsapp_clicks (source_label, property_id, viewer_ip, user_agent)
             VALUES (?, ?, ?, ?)'
        )->execute([
            substr($sourceLabel !== '' ? $sourceLabel : 'site', 0, 100),
            $propertyId,
            analyticsClientIp(),
            $ua !== '' ? $ua : null,
        ]);
    } catch (Throwable $e) {
        error_log('WhatsApp click tracking failed: ' . $e->getMessage());
    }
}

function whatsappTrackUrl(string $sourceLabel = 'footer', ?int $propertyId = null): string
{
    $query = ['src' => $sourceLabel];
    if ($propertyId !== null && $propertyId > 0) {
        $query['property_id'] = $propertyId;
    }
    return webPath('actions/whatsapp_click?' . http_build_query($query));
}
