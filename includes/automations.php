<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/notifications.php';

function createAdminAlert(
    string $type,
    string $title,
    string $body = '',
    string $severity = 'info',
    ?string $entityType = null,
    ?int $entityId = null
): void {
    try {
        $pdo = getDatabaseConnection();
        // Deduplicate similar unread alerts within 24h.
        $dup = $pdo->prepare(
            "SELECT id FROM admin_alerts
             WHERE alert_type = ? AND entity_type <=> ? AND entity_id <=> ? AND is_read = 0
               AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
             LIMIT 1"
        );
        $dup->execute([$type, $entityType, $entityId]);
        if ($dup->fetchColumn() !== false) {
            return;
        }

        $pdo->prepare(
            'INSERT INTO admin_alerts (alert_type, title, body, entity_type, entity_id, severity)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $type,
            substr($title, 0, 255),
            $body !== '' ? $body : null,
            $entityType,
            $entityId,
            in_array($severity, ['info', 'warning', 'critical'], true) ? $severity : 'info',
        ]);
    } catch (Throwable $e) {
        error_log('Admin alert failed: ' . $e->getMessage());
    }
}

function regenerateSitemap(): bool
{
    try {
        $pdo = getDatabaseConnection();
        $rows = $pdo->query(
            "SELECT slug, updated_at FROM properties
             WHERE deleted_at IS NULL AND is_active = 1 AND publication_status = 'Published'
             ORDER BY updated_at DESC"
        )->fetchAll();

        $base = rtrim((string) ($_ENV['APP_URL'] ?? 'http://127.0.0.1:8000'), '/');
        $urls = [
            ['loc' => $base . '/', 'priority' => '1.0'],
            ['loc' => $base . '/pages/properties', 'priority' => '0.9'],
            ['loc' => $base . '/pages/about', 'priority' => '0.6'],
            ['loc' => $base . '/pages/enquire', 'priority' => '0.7'],
            ['loc' => $base . '/pages/emi-calculator', 'priority' => '0.5'],
            ['loc' => $base . '/pages/terms-conditions', 'priority' => '0.3'],
            ['loc' => $base . '/pages/privacy-policy', 'priority' => '0.3'],
        ];
        foreach ($rows as $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $urls[] = [
                'loc' => $base . '/pages/property?id=' . rawurlencode($slug),
                'priority' => '0.8',
                'lastmod' => substr((string) ($row['updated_at'] ?? ''), 0, 10),
            ];
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . htmlspecialchars((string) $url['lastmod'], ENT_XML1) . "</lastmod>\n";
            }
            $xml .= '    <priority>' . htmlspecialchars((string) $url['priority'], ENT_XML1) . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";

        $path = dirname(__DIR__) . '/public/sitemap.xml';
        return file_put_contents($path, $xml) !== false;
    } catch (Throwable $e) {
        error_log('Sitemap generation failed: ' . $e->getMessage());
        return false;
    }
}

function rotateFeaturedProperties(int $keep = 3): int
{
    $pdo = getDatabaseConnection();
    $eligible = $pdo->query(
        "SELECT id FROM properties
         WHERE deleted_at IS NULL
           AND is_active = 1
           AND publication_status = 'Published'
         ORDER BY updated_at DESC, id DESC"
    )->fetchAll(PDO::FETCH_COLUMN);

    if ($eligible === []) {
        return 0;
    }

    $keep = max(1, min($keep, count($eligible)));
    // Rotate window based on day-of-year.
    $offset = ((int) date('z')) % max(1, count($eligible));
    $selected = [];
    for ($i = 0; $i < $keep; $i++) {
        $selected[] = (int) $eligible[($offset + $i) % count($eligible)];
    }

    $pdo->exec('UPDATE properties SET is_featured = 0 WHERE deleted_at IS NULL');
    $stmt = $pdo->prepare('UPDATE properties SET is_featured = 1, featured_rotated_at = NOW() WHERE id = ?');
    foreach ($selected as $id) {
        $stmt->execute([$id]);
    }

    createAdminAlert(
        'featured_rotation',
        'Featured properties rotated',
        'Auto-rotation selected ' . count($selected) . ' published listings.',
        'info'
    );

    return count($selected);
}

function runScheduledAutomations(): array
{
    $pdo = getDatabaseConnection();
    $summary = [
        'visit_reminders' => 0,
        'stale_leads' => 0,
        'rera_alerts' => 0,
        'featured_rotated' => 0,
        'sitemap' => false,
    ];

    // Site visit tomorrow reminders.
    $tomorrow = $pdo->query(
        "SELECT v.id, v.name, v.visit_date, v.visit_time, p.name AS property_name
         FROM site_visit_requests v
         LEFT JOIN properties p ON p.id = v.property_id
         WHERE v.visit_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
           AND v.status IN ('Pending','Confirmed','Rescheduled')"
    )->fetchAll();
    foreach ($tomorrow as $row) {
        createAdminAlert(
            'visit_tomorrow',
            'Site visit tomorrow: ' . (string) $row['name'],
            trim(($row['property_name'] ?? 'General') . ' · ' . ($row['visit_date'] ?? '') . ' ' . substr((string) ($row['visit_time'] ?? ''), 0, 5)),
            'warning',
            'site_visit',
            (int) $row['id']
        );
        $summary['visit_reminders']++;
    }

    // No lead follow-up for 3 days.
    $stale = $pdo->query(
        "SELECT id, name, subject, created_at
         FROM enquiries
         WHERE status = 'new'
           AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
           AND (follow_up_date IS NULL OR follow_up_date <= DATE_SUB(NOW(), INTERVAL 3 DAY))"
    )->fetchAll();
    foreach ($stale as $row) {
        createAdminAlert(
            'stale_lead',
            'No follow-up for 3 days: ' . (string) $row['name'],
            'Enquiry #' . (int) $row['id'] . ' still marked new since ' . (string) $row['created_at'],
            'critical',
            'enquiry',
            (int) $row['id']
        );
        $summary['stale_leads']++;
    }

    // RERA expiry within 30 days.
    $rera = $pdo->query(
        "SELECT id, name, rera_number, rera_expiry
         FROM properties
         WHERE deleted_at IS NULL
           AND rera_expiry IS NOT NULL
           AND rera_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
    )->fetchAll();
    foreach ($rera as $row) {
        createAdminAlert(
            'rera_expiry',
            'RERA approaching expiry: ' . (string) $row['name'],
            'RERA ' . (string) ($row['rera_number'] ?? '') . ' expires on ' . (string) $row['rera_expiry'],
            'warning',
            'property',
            (int) $row['id']
        );
        $summary['rera_alerts']++;
    }

    $summary['featured_rotated'] = rotateFeaturedProperties(3);
    $summary['sitemap'] = regenerateSitemap();

    return $summary;
}

function findDuplicateProperties(): array
{
    $pdo = getDatabaseConnection();
    return $pdo->query(
        "SELECT LOWER(TRIM(p.name)) AS norm_name, p.city_id, p.area_id,
                GROUP_CONCAT(p.id ORDER BY p.id) AS ids,
                GROUP_CONCAT(p.name ORDER BY p.id SEPARATOR ' | ') AS names,
                COUNT(*) AS dup_count
         FROM properties p
         WHERE p.deleted_at IS NULL
         GROUP BY LOWER(TRIM(p.name)), p.city_id, p.area_id
         HAVING COUNT(*) > 1
         ORDER BY dup_count DESC"
    )->fetchAll();
}

function findIncompleteProperties(): array
{
    $pdo = getDatabaseConnection();
    $rows = $pdo->query(
        "SELECT p.id, p.name, p.slug, p.publication_status, p.rera_number, p.min_price,
                (SELECT COUNT(*) FROM property_images pi WHERE pi.property_id = p.id) AS image_count,
                (SELECT COUNT(*) FROM property_amenities pa WHERE pa.property_id = p.id) AS amenity_count,
                (SELECT COUNT(*) FROM property_unit_types ut WHERE ut.property_id = p.id AND ut.floor_plan_path IS NOT NULL AND ut.floor_plan_path <> '') AS floor_plan_count
         FROM properties p
         WHERE p.deleted_at IS NULL
         ORDER BY p.updated_at DESC"
    )->fetchAll();

    $out = [];
    foreach ($rows as $row) {
        $missing = [];
        if ((int) $row['image_count'] < 1) {
            $missing[] = 'images';
        }
        if ((int) $row['floor_plan_count'] < 1) {
            $missing[] = 'floor plans';
        }
        if ((int) $row['amenity_count'] < 1) {
            $missing[] = 'amenities';
        }
        if (trim((string) ($row['rera_number'] ?? '')) === '') {
            $missing[] = 'RERA number';
        }
        if ($row['min_price'] === null || (float) $row['min_price'] <= 0) {
            $missing[] = 'price';
        }
        if ($missing === []) {
            continue;
        }
        $row['missing'] = $missing;
        $out[] = $row;
    }
    return $out;
}
