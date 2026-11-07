<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/whatsapp.php';

$pdo = getDatabaseConnection();
$monthStart = date('Y-m-01 00:00:00');
$apiReady = isWhatsAppApiConfigured();

$clickStats = [
    'today' => (int) $pdo->query('SELECT COUNT(*) FROM whatsapp_clicks WHERE DATE(clicked_at) = CURDATE()')->fetchColumn(),
    'month' => (int) $pdo->query('SELECT COUNT(*) FROM whatsapp_clicks WHERE clicked_at >= ' . $pdo->quote($monthStart))->fetchColumn(),
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM whatsapp_clicks')->fetchColumn(),
];

$leadStats = [
    'consent' => (int) $pdo->query('SELECT COUNT(*) FROM enquiries WHERE whatsapp_consent = 1')->fetchColumn(),
    'source' => (int) $pdo->query("SELECT COUNT(*) FROM enquiries WHERE source LIKE '%whatsapp%'")->fetchColumn(),
    'month_consent' => (int) $pdo->query('SELECT COUNT(*) FROM enquiries WHERE whatsapp_consent = 1 AND created_at >= ' . $pdo->quote($monthStart))->fetchColumn(),
];

$clicksBySource = $pdo->query(
    "SELECT source_label, COUNT(*) AS hits
     FROM whatsapp_clicks
     WHERE clicked_at >= {$pdo->quote($monthStart)}
     GROUP BY source_label
     ORDER BY hits DESC"
)->fetchAll();

$recentClicks = $pdo->query(
    "SELECT w.*, p.name AS property_name
     FROM whatsapp_clicks w
     LEFT JOIN properties p ON p.id = w.property_id
     ORDER BY w.clicked_at DESC
     LIMIT 30"
)->fetchAll();

$whatsappLeads = $pdo->query(
    "SELECT e.*, p.name AS property_name
     FROM enquiries e
     LEFT JOIN properties p ON p.id = e.property_id
     WHERE e.whatsapp_consent = 1 OR e.source LIKE '%whatsapp%'
     ORDER BY e.created_at DESC
     LIMIT 40"
)->fetchAll();

adminLayoutStart('WhatsApp tracking', 'whatsapp');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Lead channel</p>
        <h2>WhatsApp enquiry tracking</h2>
        <p>Tracks WhatsApp-originated clicks and consent-based leads where integration permits.</p>
    </div>
    <div class="dash-actions">
        <div class="badge <?= $apiReady ? 'published' : 'draft' ?>" style="justify-content:center;padding:10px 14px;">
            Meta API: <?= $apiReady ? 'Configured' : 'Not configured' ?>
        </div>
    </div>
</section>

<div class="stat-grid">
    <div class="stat-card"><div class="label">WA clicks today</div><div class="value"><?= $clickStats['today'] ?></div></div>
    <div class="stat-card"><div class="label">WA clicks this month</div><div class="value"><?= $clickStats['month'] ?></div></div>
    <div class="stat-card"><div class="label">Consent leads</div><div class="value"><?= $leadStats['consent'] ?></div><div class="hint"><?= $leadStats['month_consent'] ?> this month</div></div>
    <div class="stat-card"><div class="label">Source-tagged WA</div><div class="value"><?= $leadStats['source'] ?></div><div class="hint"><?= $clickStats['total'] ?> total clicks logged</div></div>
</div>

<div class="dash-grid">
    <div class="panel">
        <h3 style="margin-top:0;">Click sources this month</h3>
        <div class="pulse-list">
            <?php if ($clicksBySource === []): ?>
                <div class="pulse-item"><div><strong>No WhatsApp clicks yet</strong><span>Footer and floating chat buttons log through the tracker.</span></div></div>
            <?php else: ?>
                <?php foreach ($clicksBySource as $row): ?>
                    <div class="pulse-item">
                        <div>
                            <strong><?= h((string) $row['source_label']) ?></strong>
                            <span>Outbound WhatsApp opens</span>
                        </div>
                        <span class="badge published"><?= (int) $row['hits'] ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p class="help" style="margin-top:12px;">
            Click tracking works immediately. Full inbound WhatsApp message capture needs Meta Cloud API webhooks when you enable them.
        </p>
    </div>

    <div class="panel">
        <h3 style="margin-top:0;">Recent WhatsApp clicks</h3>
        <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
            <table>
                <thead><tr><th>When</th><th>Source</th><th>Property</th><th>IP</th></tr></thead>
                <tbody>
                <?php if ($recentClicks === []): ?>
                    <tr><td colspan="4" class="muted">No clicks logged.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentClicks as $row): ?>
                        <tr>
                            <td class="muted"><?= h((string) $row['clicked_at']) ?></td>
                            <td><?= h((string) $row['source_label']) ?></td>
                            <td><?= h((string) ($row['property_name'] ?? '—')) ?></td>
                            <td class="muted"><?= h((string) ($row['viewer_ip'] ?? '—')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel" style="margin-top:16px;">
    <div class="toolbar" style="margin-bottom:8px;">
        <h3 style="margin:0;">WhatsApp-related enquiries</h3>
        <a class="btn small secondary" href="<?= h(adminUrl('enquiries/index')) ?>">All enquiries</a>
    </div>
    <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
        <table>
            <thead><tr><th>Lead</th><th>Property</th><th>Source</th><th>Consent</th><th>When</th></tr></thead>
            <tbody>
            <?php if ($whatsappLeads === []): ?>
                <tr><td colspan="5" class="muted">No WhatsApp-consent or WhatsApp-source leads yet.</td></tr>
            <?php else: ?>
                <?php foreach ($whatsappLeads as $row): ?>
                    <tr>
                        <td>
                            <a href="<?= h(adminUrl('enquiries/index?id=' . (int) $row['id'])) ?>"><strong><?= h((string) $row['name']) ?></strong></a>
                            <div class="muted"><?= h((string) $row['phone']) ?></div>
                        </td>
                        <td><?= h((string) ($row['property_name'] ?? '—')) ?></td>
                        <td class="muted"><?= h((string) $row['source']) ?></td>
                        <td><?= (int) $row['whatsapp_consent'] === 1 ? '<span class="badge published">Yes</span>' : '<span class="badge draft">No</span>' ?></td>
                        <td class="muted"><?= h((string) $row['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
