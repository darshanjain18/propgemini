<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

$pdo = getDatabaseConnection();
$q = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT l.*, a.name AS admin_name, a.email AS admin_email
        FROM admin_activity_logs l
        LEFT JOIN admins a ON a.id = l.admin_id
        WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (l.action LIKE :q OR l.entity_type LIKE :q OR l.details LIKE :q OR a.name LIKE :q OR a.email LIKE :q)';
    $params['q'] = '%' . $q . '%';
}
$sql .= ' ORDER BY l.created_at DESC, l.id DESC LIMIT 250';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$today = (int) $pdo->query('SELECT COUNT(*) FROM admin_activity_logs WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$week = (int) $pdo->query('SELECT COUNT(*) FROM admin_activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
$total = (int) $pdo->query('SELECT COUNT(*) FROM admin_activity_logs')->fetchColumn();

adminLayoutStart('Recent activity', 'activity');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Audit trail</p>
        <h2>Recent activity</h2>
        <p>Tracks admin changes and updates across properties, enquiries, visits, legal pages and settings.</p>
    </div>
</section>

<div class="stat-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));">
    <div class="stat-card"><div class="label">Today</div><div class="value"><?= $today ?></div></div>
    <div class="stat-card"><div class="label">Last 7 days</div><div class="value"><?= $week ?></div></div>
    <div class="stat-card"><div class="label">All time</div><div class="value"><?= $total ?></div></div>
</div>

<div class="toolbar">
    <form class="filters filters-row" method="get">
        <input type="search" name="q" placeholder="Search action, admin, details" value="<?= h($q) ?>">
        <button class="btn secondary" type="submit">Search</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
        <tr><th>When</th><th>Admin</th><th>Action</th><th>Entity</th><th>Details</th></tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr><td colspan="5" class="muted">No admin activity logged yet.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="muted"><?= h((string) $row['created_at']) ?></td>
                    <td>
                        <strong><?= h((string) ($row['admin_name'] ?? 'System')) ?></strong>
                        <div class="muted"><?= h((string) ($row['admin_email'] ?? '')) ?></div>
                    </td>
                    <td><span class="badge contacted"><?= h((string) $row['action']) ?></span></td>
                    <td class="muted">
                        <?= h((string) ($row['entity_type'] ?? '—')) ?>
                        <?php if (!empty($row['entity_id'])): ?>#<?= (int) $row['entity_id'] ?><?php endif; ?>
                    </td>
                    <td><?= h((string) ($row['details'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php adminLayoutEnd(); ?>
