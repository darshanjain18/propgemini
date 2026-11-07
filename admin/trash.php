<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/automations.php';

$pdo = getDatabaseConnection();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    requireAdmin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('trash.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($id > 0 && $action === 'restore') {
        $pdo->prepare(
            "UPDATE properties
             SET deleted_at = NULL,
                 is_active = 1,
                 publication_status = 'Draft'
             WHERE id = ?"
        )->execute([$id]);
        logAdminActivity('property_restore', 'property', $id, 'Restored from trash');
        createAdminAlert('property_restored', 'Property restored from trash', 'Property #' . $id . ' restored as Draft.', 'info', 'property', $id);
        adminFlash('success', 'Property restored as Draft.');
    }
    adminRedirect('trash.php');
}

$rows = $pdo->query(
    "SELECT p.id, p.name, p.slug, p.deleted_at, p.publication_status, c.name AS city_name, a.name AS area_name
     FROM properties p
     INNER JOIN cities c ON c.id = p.city_id
     INNER JOIN areas a ON a.id = p.area_id
     WHERE p.deleted_at IS NOT NULL
     ORDER BY p.deleted_at DESC
     LIMIT 200"
)->fetchAll();

adminLayoutStart('Deleted recovery', 'trash');
?>
<section class="dash-hero" style="margin-bottom:16px;">
    <div>
        <p class="eyebrow">Soft delete recovery</p>
        <h2>Deleted property recovery</h2>
        <p>Soft-deleted listings stay in the database. Restore them here without permanent loss.</p>
    </div>
</section>

<div class="table-wrap">
    <table>
        <thead><tr><th>Property</th><th>Location</th><th>Deleted</th><th></th></tr></thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr><td colspan="4" class="muted">Trash is empty.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <strong><?= h((string) $row['name']) ?></strong>
                        <div class="muted"><?= h((string) $row['slug']) ?></div>
                    </td>
                    <td><?= h((string) $row['area_name']) ?>, <?= h((string) $row['city_name']) ?></td>
                    <td class="muted"><?= h((string) $row['deleted_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Restore this property as Draft?');">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="btn small" type="submit">Restore</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php adminLayoutEnd(); ?>
