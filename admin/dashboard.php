<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/notifications.php';

$pdo = getDatabaseConnection();
$contact = contactDetails();
$contactReady = $contact['has_phone'] && $contact['has_email'] && $contact['has_whatsapp'];
$monthStart = date('Y-m-01 00:00:00');

$counts = [
    'properties' => (int) $pdo->query('SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL')->fetchColumn(),
    'featured' => (int) $pdo->query('SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL AND is_featured = 1')->fetchColumn(),
    'pending_publish' => (int) $pdo->query("SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL AND publication_status IN ('Draft','Unpublished')")->fetchColumn(),
    'views_month' => (int) $pdo->query('SELECT COUNT(*) FROM property_views WHERE viewed_at >= ' . $pdo->quote($monthStart))->fetchColumn(),
    'enquiries_new' => (int) $pdo->query("SELECT COUNT(*) FROM enquiries WHERE status = 'new'")->fetchColumn(),
    'enquiries_total' => (int) $pdo->query('SELECT COUNT(*) FROM enquiries')->fetchColumn(),
    'visits_pending' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE status = 'Pending'")->fetchColumn(),
    'visits_today' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE visit_date = CURDATE() AND status IN ('Pending','Confirmed','Rescheduled')")->fetchColumn(),
    'visits_upcoming' => (int) $pdo->query("SELECT COUNT(*) FROM site_visit_requests WHERE visit_date >= CURDATE() AND status IN ('Pending','Confirmed','Rescheduled')")->fetchColumn(),
];

$chartDays = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime('-' . $i . ' days'));
    $chartDays[$date] = [
        'label' => date('j M', strtotime($date)),
        'leads' => 0,
        'visits' => 0,
    ];
}

$leadRows = $pdo->query(
    "SELECT DATE(created_at) AS day_key, COUNT(*) AS total
     FROM enquiries
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();
foreach ($leadRows as $row) {
    $key = (string) $row['day_key'];
    if (isset($chartDays[$key])) {
        $chartDays[$key]['leads'] = (int) $row['total'];
    }
}

$visitRows = $pdo->query(
    "SELECT DATE(created_at) AS day_key, COUNT(*) AS total
     FROM site_visit_requests
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();
foreach ($visitRows as $row) {
    $key = (string) $row['day_key'];
    if (isset($chartDays[$key])) {
        $chartDays[$key]['visits'] = (int) $row['total'];
    }
}

$chartMax = 1;
foreach ($chartDays as $day) {
    $chartMax = max($chartMax, $day['leads'], $day['visits']);
}

$recentEnquiries = $pdo->query(
    "SELECT e.id, e.name, e.subject, e.status, e.created_at, p.name AS property_name
     FROM enquiries e
     LEFT JOIN properties p ON p.id = e.property_id
     ORDER BY e.created_at DESC
     LIMIT 6"
)->fetchAll();

$upcomingVisits = $pdo->query(
    "SELECT v.id, v.name, v.visit_date, v.visit_time, v.status, p.name AS property_name
     FROM site_visit_requests v
     LEFT JOIN properties p ON p.id = v.property_id
     WHERE v.visit_date >= CURDATE()
       AND v.status IN ('Pending','Confirmed','Rescheduled')
     ORDER BY v.visit_date ASC, v.visit_time ASC, v.id ASC
     LIMIT 6"
)->fetchAll();

adminLayoutStart('Dashboard', 'dashboard');
?>
<?php if (!$contactReady): ?>
    <div class="flash flash-error" style="margin-top:0;">
        Contact details are incomplete — update phone, WhatsApp and email in
        <a class="text-link" href="<?= h(adminUrl('settings.php')) ?>">Settings</a>
        or follow the
        <a class="text-link" href="<?= h(adminUrl('guide.php')) ?>">getting started guide</a>.
    </div>
<?php endif; ?>
<section class="dash-hero">
    <div>
        <p class="eyebrow">Overview</p>
        <h2>Dashboard</h2>
        <p>Listings, leads and site visits at a glance.</p>
    </div>
    <div class="dash-actions">
        <a class="btn" href="<?= h(adminUrl('properties/edit')) ?>">Add property</a>
        <a class="btn secondary" href="<?= h(adminUrl('enquiries/index?status=new')) ?>">New enquiries</a>
        <a class="btn secondary" href="<?= h(adminUrl('guide.php')) ?>">Getting started</a>
    </div>
</section>

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">Properties</div>
        <div class="value"><?= $counts['properties'] ?></div>
        <div class="hint"><?= $counts['featured'] ?> featured · <?= $counts['pending_publish'] ?> draft</div>
    </div>
    <div class="stat-card">
        <div class="label">Views this month</div>
        <div class="value"><?= $counts['views_month'] ?></div>
        <div class="hint">Property page views</div>
    </div>
    <div class="stat-card">
        <div class="label">New enquiries</div>
        <div class="value"><?= $counts['enquiries_new'] ?></div>
        <div class="hint"><?= $counts['enquiries_total'] ?> total leads</div>
    </div>
    <div class="stat-card">
        <div class="label">Site visits</div>
        <div class="value"><?= $counts['visits_today'] ?></div>
        <div class="hint"><?= $counts['visits_pending'] ?> pending · <?= $counts['visits_upcoming'] ?> upcoming</div>
    </div>
</div>

<section class="dash-card dash-chart-card">
    <div class="toolbar" style="margin-bottom:12px;">
        <div>
            <h3 style="margin:0;">Leads &amp; site visits</h3>
            <p class="muted" style="margin:4px 0 0;font-size:13px;">Last 14 days</p>
        </div>
        <div class="chart-legend">
            <span class="chart-legend-item chart-legend-leads">Enquiries</span>
            <span class="chart-legend-item chart-legend-visits">Visits</span>
        </div>
    </div>
    <div class="dash-chart">
        <?php foreach ($chartDays as $day): ?>
            <?php
            $leadH = (int) round(($day['leads'] / $chartMax) * 100);
            $visitH = (int) round(($day['visits'] / $chartMax) * 100);
            ?>
            <div class="dash-chart-col" title="<?= h($day['label']) ?>: <?= $day['leads'] ?> leads, <?= $day['visits'] ?> visits">
                <div class="dash-chart-bars">
                    <span class="dash-chart-bar leads" style="height:<?= max(4, $leadH) ?>%"></span>
                    <span class="dash-chart-bar visits" style="height:<?= max(4, $visitH) ?>%"></span>
                </div>
                <span><?= h($day['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="dash-grid">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:8px;">
            <h3 style="margin:0;">Latest enquiries</h3>
            <a class="btn small secondary" href="<?= h(adminUrl('enquiries/index')) ?>">View all</a>
        </div>
        <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
            <table>
                <thead><tr><th>Lead</th><th>Property</th><th>Status</th></tr></thead>
                <tbody>
                <?php if ($recentEnquiries === []): ?>
                    <tr><td colspan="3" class="muted">No enquiries yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentEnquiries as $row): ?>
                        <tr>
                            <td>
                                <a href="<?= h(adminUrl('enquiries/index?id=' . (int) $row['id'])) ?>"><strong><?= h((string) $row['name']) ?></strong></a>
                                <div class="muted"><?= h(substr((string) $row['created_at'], 0, 16)) ?></div>
                            </td>
                            <td><?= h((string) ($row['property_name'] ?? '—')) ?></td>
                            <td><span class="badge <?= h((string) $row['status']) ?>"><?= h((string) $row['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="toolbar" style="margin-bottom:8px;">
            <h3 style="margin:0;">Upcoming visits</h3>
            <a class="btn small secondary" href="<?= h(adminUrl('visits/index')) ?>">Pipeline</a>
        </div>
        <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
            <table>
                <thead><tr><th>Visitor</th><th>When</th><th>Status</th></tr></thead>
                <tbody>
                <?php if ($upcomingVisits === []): ?>
                    <tr><td colspan="3" class="muted">No upcoming visits.</td></tr>
                <?php else: ?>
                    <?php foreach ($upcomingVisits as $row): ?>
                        <tr>
                            <td>
                                <a href="<?= h(adminUrl('visits/index?id=' . (int) $row['id'])) ?>"><strong><?= h((string) $row['name']) ?></strong></a>
                                <div class="muted"><?= h((string) ($row['property_name'] ?? '—')) ?></div>
                            </td>
                            <td class="muted">
                                <?= h((string) $row['visit_date']) ?>
                                <?php if (!empty($row['visit_time'])): ?>
                                    <div><?= h(substr((string) $row['visit_time'], 0, 5)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= h(strtolower((string) $row['status'])) ?>"><?= h((string) $row['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php adminLayoutEnd(); ?>
