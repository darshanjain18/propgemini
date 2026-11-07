<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$pdo = getDatabaseConnection();
$monthStart = date('Y-m-01 00:00:00');
$todayStart = date('Y-m-d 00:00:00');

$stats = [
    'today' => (int) $pdo->query('SELECT COUNT(*) FROM site_traffic WHERE viewed_at >= ' . $pdo->quote($todayStart))->fetchColumn(),
    'month' => (int) $pdo->query('SELECT COUNT(*) FROM site_traffic WHERE viewed_at >= ' . $pdo->quote($monthStart))->fetchColumn(),
    'unique_month' => (int) $pdo->query('SELECT COUNT(DISTINCT viewer_ip) FROM site_traffic WHERE viewed_at >= ' . $pdo->quote($monthStart) . ' AND viewer_ip IS NOT NULL')->fetchColumn(),
    'property_views_month' => (int) $pdo->query('SELECT COUNT(*) FROM property_views WHERE viewed_at >= ' . $pdo->quote($monthStart))->fetchColumn(),
];

$topPages = $pdo->query(
    "SELECT path, page_title, COUNT(*) AS hits
     FROM site_traffic
     WHERE viewed_at >= {$pdo->quote($monthStart)}
     GROUP BY path, page_title
     ORDER BY hits DESC
     LIMIT 12"
)->fetchAll();

$topProperties = $pdo->query(
    "SELECT p.id, p.name, c.name AS city_name, a.name AS area_name, COUNT(v.id) AS views
     FROM property_views v
     INNER JOIN properties p ON p.id = v.property_id
     INNER JOIN cities c ON c.id = p.city_id
     INNER JOIN areas a ON a.id = p.area_id
     WHERE v.viewed_at >= {$pdo->quote($monthStart)}
       AND p.deleted_at IS NULL
     GROUP BY p.id, p.name, c.name, a.name
     ORDER BY views DESC
     LIMIT 10"
)->fetchAll();

$daily = $pdo->query(
    "SELECT DATE(viewed_at) AS day, COUNT(*) AS hits
     FROM site_traffic
     WHERE viewed_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(viewed_at)
     ORDER BY day ASC"
)->fetchAll();
$dailyMap = [];
foreach ($daily as $row) {
    $dailyMap[(string) $row['day']] = (int) $row['hits'];
}
$chartDays = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime('-' . $i . ' days'));
    $chartDays[] = ['day' => $day, 'hits' => $dailyMap[$day] ?? 0];
}
$maxHits = max(array_column($chartDays, 'hits') ?: [1]);

$recent = $pdo->query(
    "SELECT path, page_title, viewer_ip, referrer, viewed_at
     FROM site_traffic
     ORDER BY viewed_at DESC
     LIMIT 40"
)->fetchAll();

adminLayoutStart('Website traffic', 'traffic');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Analytics</p>
        <h2>Website traffic</h2>
        <p>Tracks visitors and property views across the public site.</p>
    </div>
</section>

<div class="stat-grid">
    <div class="stat-card"><div class="label">Visits today</div><div class="value"><?= $stats['today'] ?></div></div>
    <div class="stat-card"><div class="label">Visits this month</div><div class="value"><?= $stats['month'] ?></div></div>
    <div class="stat-card"><div class="label">Unique IPs (month)</div><div class="value"><?= $stats['unique_month'] ?></div></div>
    <div class="stat-card"><div class="label">Property views (month)</div><div class="value"><?= $stats['property_views_month'] ?></div></div>
</div>

<div class="panel" style="margin-bottom:16px;">
    <h3 style="margin-top:0;">Last 14 days</h3>
    <div class="traffic-bars">
        <?php foreach ($chartDays as $point): ?>
            <?php $height = $maxHits > 0 ? max(4, (int) round(($point['hits'] / $maxHits) * 100)) : 4; ?>
            <div class="traffic-bar-col" title="<?= h($point['day'] . ': ' . $point['hits']) ?>">
                <div class="traffic-bar" style="height:<?= $height ?>%"></div>
                <span><?= h(date('d', strtotime($point['day']))) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="dash-grid">
    <div class="panel">
        <h3 style="margin-top:0;">Top pages this month</h3>
        <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
            <table>
                <thead><tr><th>Page</th><th>Hits</th></tr></thead>
                <tbody>
                <?php if ($topPages === []): ?>
                    <tr><td colspan="2" class="muted">No traffic recorded yet. Browse the public site to start collecting data.</td></tr>
                <?php else: ?>
                    <?php foreach ($topPages as $row): ?>
                        <tr>
                            <td>
                                <strong><?= h((string) ($row['page_title'] ?: $row['path'])) ?></strong>
                                <div class="muted"><?= h((string) $row['path']) ?></div>
                            </td>
                            <td><?= (int) $row['hits'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top:0;">Top properties this month</h3>
        <div class="pulse-list">
            <?php if ($topProperties === []): ?>
                <div class="pulse-item"><div><strong>No property views yet</strong><span>Open listing pages publicly to populate this list.</span></div></div>
            <?php else: ?>
                <?php foreach ($topProperties as $row): ?>
                    <a class="pulse-item" href="<?= h(adminUrl('properties/edit?id=' . (int) $row['id'])) ?>">
                        <div>
                            <strong><?= h((string) $row['name']) ?></strong>
                            <span><?= h((string) $row['area_name']) ?>, <?= h((string) $row['city_name']) ?></span>
                        </div>
                        <span class="badge published"><?= (int) $row['views'] ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel" style="margin-top:16px;">
    <h3 style="margin-top:0;">Recent visitors</h3>
    <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
        <table>
            <thead><tr><th>When</th><th>Page</th><th>IP</th><th>Referrer</th></tr></thead>
            <tbody>
            <?php if ($recent === []): ?>
                <tr><td colspan="4" class="muted">No recent hits.</td></tr>
            <?php else: ?>
                <?php foreach ($recent as $row): ?>
                    <tr>
                        <td class="muted"><?= h((string) $row['viewed_at']) ?></td>
                        <td>
                            <strong><?= h((string) ($row['page_title'] ?: $row['path'])) ?></strong>
                            <div class="muted"><?= h((string) $row['path']) ?></div>
                        </td>
                        <td class="muted"><?= h((string) ($row['viewer_ip'] ?? '—')) ?></td>
                        <td class="muted"><?= h((string) ($row['referrer'] ?? '—')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
