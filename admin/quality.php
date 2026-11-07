<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/automations.php';

$pdo = getDatabaseConnection();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('quality.php');
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'rotate_featured') {
        $count = rotateFeaturedProperties(3);
        logAdminActivity('featured_rotation', 'property', null, 'Rotated ' . $count);
        adminFlash('success', 'Featured properties rotated (' . $count . ').');
    }
    if ($action === 'run_automations') {
        $summary = runScheduledAutomations();
        logAdminActivity('run_automations', null, null, json_encode($summary));
        adminFlash('success', 'Automations ran: reminders ' . $summary['visit_reminders'] . ', stale leads ' . $summary['stale_leads'] . ', RERA ' . $summary['rera_alerts'] . '.');
    }
    adminRedirect('quality.php');
}

$duplicates = findDuplicateProperties();
$incomplete = findIncompleteProperties();
$featured = $pdo->query(
    "SELECT id, name, featured_rotated_at, publication_status
     FROM properties
     WHERE deleted_at IS NULL AND is_featured = 1
     ORDER BY featured_rotated_at DESC, id DESC"
)->fetchAll();

adminLayoutStart('Listing quality', 'quality');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Portfolio hygiene</p>
        <h2>Listing quality</h2>
        <p>Duplicate detection, incomplete listing alerts and featured rotation.</p>
    </div>
    <div class="dash-actions">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="action" value="rotate_featured">
            <button class="btn" type="submit">Rotate featured</button>
        </form>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="action" value="run_automations">
            <button class="btn secondary" type="submit">Run automations</button>
        </form>
    </div>
</section>

<div class="stat-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));">
    <div class="stat-card"><div class="label">Duplicate groups</div><div class="value"><?= count($duplicates) ?></div></div>
    <div class="stat-card"><div class="label">Incomplete listings</div><div class="value"><?= count($incomplete) ?></div></div>
</div>

<div class="dash-grid">
    <div class="panel">
        <h3 style="margin-top:0;">Duplicate listing detection</h3>
        <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
            <table>
                <thead><tr><th>Matches</th><th>IDs</th><th>Count</th></tr></thead>
                <tbody>
                <?php if ($duplicates === []): ?>
                    <tr><td colspan="3" class="muted">No duplicate name/city/area groups found.</td></tr>
                <?php else: ?>
                    <?php foreach ($duplicates as $row): ?>
                        <tr>
                            <td><?= h((string) $row['names']) ?></td>
                            <td class="muted">
                                <?php foreach (explode(',', (string) $row['ids']) as $pid): ?>
                                    <a href="<?= h(adminUrl('properties/edit?id=' . (int) $pid)) ?>">#<?= (int) $pid ?></a>
                                <?php endforeach; ?>
                            </td>
                            <td><?= (int) $row['dup_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin-top:0;">Featured rotation</h3>
        <div class="pulse-list">
            <?php if ($featured === []): ?>
                <div class="pulse-item"><div><strong>No featured homes</strong><span>Run rotation or mark listings featured.</span></div></div>
            <?php else: ?>
                <?php foreach ($featured as $row): ?>
                    <a class="pulse-item" href="<?= h(adminUrl('properties/edit?id=' . (int) $row['id'])) ?>">
                        <div>
                            <strong><?= h((string) $row['name']) ?></strong>
                            <span>Rotated <?= h((string) ($row['featured_rotated_at'] ?? 'manual')) ?></span>
                        </div>
                        <span class="badge published">Featured</span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="panel" style="margin-top:16px;">
    <h3 style="margin-top:0;">Incomplete listing alerts</h3>
    <div class="table-wrap" style="border:0;box-shadow:none;background:transparent;">
        <table>
            <thead><tr><th>Property</th><th>Status</th><th>Missing</th></tr></thead>
            <tbody>
            <?php if ($incomplete === []): ?>
                <tr><td colspan="3" class="muted">All active listings look complete.</td></tr>
            <?php else: ?>
                <?php foreach ($incomplete as $row): ?>
                    <tr>
                        <td><a href="<?= h(adminUrl('properties/edit?id=' . (int) $row['id'])) ?>"><strong><?= h((string) $row['name']) ?></strong></a></td>
                        <td><span class="badge <?= h(strtolower((string) $row['publication_status'])) ?>"><?= h((string) $row['publication_status']) ?></span></td>
                        <td class="muted"><?= h(implode(', ', $row['missing'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
