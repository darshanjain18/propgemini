<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/automations.php';

$pdo = getDatabaseConnection();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('alerts.php');
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'mark_read') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('UPDATE admin_alerts SET is_read = 1 WHERE id = ?')->execute([$id]);
    }
    if ($action === 'mark_all') {
        $pdo->exec('UPDATE admin_alerts SET is_read = 1 WHERE is_read = 0');
    }
    if ($action === 'run') {
        runScheduledAutomations();
        adminFlash('success', 'Automation checks completed.');
    }
    adminRedirect('alerts.php');
}

$unread = (int) $pdo->query('SELECT COUNT(*) FROM admin_alerts WHERE is_read = 0')->fetchColumn();
$rows = $pdo->query(
    'SELECT * FROM admin_alerts ORDER BY is_read ASC, created_at DESC LIMIT 200'
)->fetchAll();

adminLayoutStart('Alerts & automations', 'alerts');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Operations</p>
        <h2>Alerts &amp; automations</h2>
        <p>New enquiry/visit alerts, tomorrow reminders, stale lead warnings, RERA expiry and publish triggers.</p>
    </div>
    <div class="dash-actions">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="action" value="run">
            <button class="btn" type="submit">Run checks now</button>
        </form>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="action" value="mark_all">
            <button class="btn secondary" type="submit">Mark all read</button>
        </form>
    </div>
</section>

<div class="stat-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));">
    <div class="stat-card"><div class="label">Unread alerts</div><div class="value"><?= $unread ?></div></div>
    <div class="stat-card"><div class="label">Total alerts</div><div class="value"><?= count($rows) ?></div></div>
</div>

<div class="table-wrap">
    <table>
        <thead><tr><th>When</th><th>Type</th><th>Alert</th><th>Severity</th><th></th></tr></thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr><td colspan="5" class="muted">No alerts yet. New enquiries, visits and scheduled checks will appear here.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr<?= (int) $row['is_read'] === 0 ? ' style="background:#fff8eb;"' : '' ?>>
                    <td class="muted"><?= h((string) $row['created_at']) ?></td>
                    <td><span class="badge contacted"><?= h((string) $row['alert_type']) ?></span></td>
                    <td>
                        <strong><?= h((string) $row['title']) ?></strong>
                        <div class="muted"><?= h((string) ($row['body'] ?? '')) ?></div>
                    </td>
                    <td><span class="badge <?= h((string) $row['severity'] === 'critical' ? 'cancelled' : ((string) $row['severity'] === 'warning' ? 'pending' : 'published')) ?>"><?= h((string) $row['severity']) ?></span></td>
                    <td>
                        <?php if ((int) $row['is_read'] === 0): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button class="btn small secondary" type="submit">Read</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Read</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php adminLayoutEnd(); ?>
