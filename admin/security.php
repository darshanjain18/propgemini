<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/admin_security.php';

$pdo = getDatabaseConnection();
$tab = (string) ($_GET['tab'] ?? 'logins');
if (!in_array($tab, ['logins', 'sessions', 'activity', 'edits'], true)) {
    $tab = 'logins';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('security.php', ['tab' => $tab]);
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'revoke_session') {
        $sid = (string) ($_POST['session_id'] ?? '');
        if ($sid !== '') {
            revokeAdminSession($sid);
            logAdminActivity('session_revoke', 'admin_session', null, $sid);
            adminFlash('success', 'Session revoked.');
        }
    }
    adminRedirect('security.php', ['tab' => $tab]);
}

$loginFilter = (string) ($_GET['login'] ?? 'all');
$loginSql = 'SELECT * FROM admin_login_logs WHERE 1=1';
if ($loginFilter === 'failed') {
    $loginSql .= ' AND success = 0';
} elseif ($loginFilter === 'success') {
    $loginSql .= ' AND success = 1';
}
$loginSql .= ' ORDER BY created_at DESC LIMIT 200';
$logins = $pdo->query($loginSql)->fetchAll();

$sessions = $pdo->query(
    "SELECT s.*, a.name AS admin_name, a.email AS admin_email
     FROM admin_sessions s
     LEFT JOIN admins a ON a.id = s.admin_id
     ORDER BY s.last_seen_at DESC
     LIMIT 100"
)->fetchAll();

$activity = $pdo->query(
    "SELECT l.*, a.name AS admin_name
     FROM admin_activity_logs l
     LEFT JOIN admins a ON a.id = l.admin_id
     ORDER BY l.created_at DESC
     LIMIT 150"
)->fetchAll();

$edits = $pdo->query(
    "SELECT h.*, p.name AS property_name, a.name AS admin_name
     FROM property_edit_history h
     LEFT JOIN properties p ON p.id = h.property_id
     LEFT JOIN admins a ON a.id = h.admin_id
     ORDER BY h.created_at DESC
     LIMIT 150"
)->fetchAll();

$failedToday = (int) $pdo->query('SELECT COUNT(*) FROM admin_login_logs WHERE success = 0 AND DATE(created_at) = CURDATE()')->fetchColumn();
$activeSessions = (int) $pdo->query('SELECT COUNT(*) FROM admin_sessions WHERE revoked_at IS NULL AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)')->fetchColumn();

adminLayoutStart('Security & audit', 'security');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Admin security</p>
        <h2>Security &amp; audit centre</h2>
        <p>Login history, failed attempts, session management, activity logs and property edit history.</p>
    </div>
</section>

<div class="stat-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));">
    <div class="stat-card"><div class="label">Failed logins today</div><div class="value"><?= $failedToday ?></div></div>
    <div class="stat-card"><div class="label">Active sessions (24h)</div><div class="value"><?= $activeSessions ?></div></div>
    <div class="stat-card"><div class="label">Edit history rows</div><div class="value"><?= count($edits) ?></div></div>
</div>

<div class="legal-tabs" style="margin:12px 0 16px;">
    <a class="btn small <?= $tab === 'logins' ? '' : 'secondary' ?>" href="<?= h(adminUrl('security?tab=logins')) ?>">Login history</a>
    <a class="btn small <?= $tab === 'sessions' ? '' : 'secondary' ?>" href="<?= h(adminUrl('security?tab=sessions')) ?>">Sessions</a>
    <a class="btn small <?= $tab === 'activity' ? '' : 'secondary' ?>" href="<?= h(adminUrl('security?tab=activity')) ?>">Activity logs</a>
    <a class="btn small <?= $tab === 'edits' ? '' : 'secondary' ?>" href="<?= h(adminUrl('security?tab=edits')) ?>">Property edits</a>
</div>

<?php if ($tab === 'logins'): ?>
    <div class="toolbar">
        <form class="filters filters-row" method="get">
            <input type="hidden" name="tab" value="logins">
            <select name="login">
                <option value="all"<?= $loginFilter === 'all' ? ' selected' : '' ?>>All attempts</option>
                <option value="success"<?= $loginFilter === 'success' ? ' selected' : '' ?>>Successful</option>
                <option value="failed"<?= $loginFilter === 'failed' ? ' selected' : '' ?>>Failed</option>
            </select>
            <button class="btn secondary" type="submit">Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Email</th><th>Result</th><th>IP</th><th>Agent</th></tr></thead>
            <tbody>
            <?php if ($logins === []): ?>
                <tr><td colspan="5" class="muted">No login attempts logged yet.</td></tr>
            <?php else: ?>
                <?php foreach ($logins as $row): ?>
                    <tr>
                        <td class="muted"><?= h((string) $row['created_at']) ?></td>
                        <td><?= h((string) $row['email']) ?></td>
                        <td><span class="badge <?= (int) $row['success'] ? 'published' : 'cancelled' ?>"><?= (int) $row['success'] ? 'Success' : 'Failed' ?></span></td>
                        <td class="muted"><?= h((string) ($row['ip_address'] ?? '—')) ?></td>
                        <td class="muted"><?= h(substr((string) ($row['user_agent'] ?? '—'), 0, 60)) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($tab === 'sessions'): ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Admin</th><th>Session</th><th>Last seen</th><th>IP</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($sessions as $row): ?>
                <tr>
                    <td>
                        <strong><?= h((string) ($row['admin_name'] ?? 'Admin')) ?></strong>
                        <div class="muted"><?= h((string) ($row['admin_email'] ?? '')) ?></div>
                    </td>
                    <td class="muted"><?= h(substr((string) $row['session_id'], 0, 16)) ?>…</td>
                    <td class="muted"><?= h((string) $row['last_seen_at']) ?></td>
                    <td class="muted"><?= h((string) ($row['ip_address'] ?? '—')) ?></td>
                    <td>
                        <?php if ($row['revoked_at'] !== null): ?>
                            <span class="badge cancelled">Revoked</span>
                        <?php else: ?>
                            <span class="badge published">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['revoked_at'] === null): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="action" value="revoke_session">
                                <input type="hidden" name="session_id" value="<?= h((string) $row['session_id']) ?>">
                                <button class="btn small danger" type="submit">Revoke</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif ($tab === 'activity'): ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Entity</th><th>Details</th></tr></thead>
            <tbody>
            <?php foreach ($activity as $row): ?>
                <tr>
                    <td class="muted"><?= h((string) $row['created_at']) ?></td>
                    <td><?= h((string) ($row['admin_name'] ?? 'System')) ?></td>
                    <td><span class="badge contacted"><?= h((string) $row['action']) ?></span></td>
                    <td class="muted"><?= h((string) ($row['entity_type'] ?? '—')) ?><?= !empty($row['entity_id']) ? ' #' . (int) $row['entity_id'] : '' ?></td>
                    <td><?= h((string) ($row['details'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>Property</th><th>Admin</th><th>Summary</th></tr></thead>
            <tbody>
            <?php if ($edits === []): ?>
                <tr><td colspan="4" class="muted">No property edit history yet.</td></tr>
            <?php else: ?>
                <?php foreach ($edits as $row): ?>
                    <tr>
                        <td class="muted"><?= h((string) $row['created_at']) ?></td>
                        <td><a href="<?= h(adminUrl('properties/edit?id=' . (int) $row['property_id'])) ?>"><?= h((string) ($row['property_name'] ?? ('#' . $row['property_id']))) ?></a></td>
                        <td><?= h((string) ($row['admin_name'] ?? 'Admin')) ?></td>
                        <td><?= h((string) $row['change_summary']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php adminLayoutEnd(); ?>
