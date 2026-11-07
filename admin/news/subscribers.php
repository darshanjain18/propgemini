<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();
$pdo = getDatabaseConnection();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="newsletter-subscribers.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['email', 'status', 'source', 'subscribed_at', 'unsubscribed_at']);
    $rows = $pdo->query('SELECT email, status, source, subscribed_at, unsubscribed_at FROM newsletter_subscribers ORDER BY subscribed_at DESC')->fetchAll();
    foreach ($rows as $row) {
        fputcsv($out, [
            (string) $row['email'],
            (string) $row['status'],
            (string) $row['source'],
            (string) $row['subscribed_at'],
            (string) ($row['unsubscribed_at'] ?? ''),
        ]);
    }
    fclose($out);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        adminFlash('error', 'Invalid request.');
        adminRedirect('news/subscribers.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if ($id > 0 && in_array($status, ['active', 'unsubscribed'], true)) {
        if ($status === 'unsubscribed') {
            $pdo->prepare('UPDATE newsletter_subscribers SET status = ?, unsubscribed_at = NOW() WHERE id = ?')
                ->execute([$status, $id]);
        } else {
            $pdo->prepare('UPDATE newsletter_subscribers SET status = ?, unsubscribed_at = NULL WHERE id = ?')
                ->execute([$status, $id]);
        }
        adminFlash('success', 'Subscriber updated.');
    }
    adminRedirect('news/subscribers.php');
}

$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT * FROM newsletter_subscribers WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND email LIKE ?';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY subscribed_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();

adminLayoutStart('Newsletter subscribers', 'news');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:0;">
            <p>Emails collected from the News page subscribe strip. Delivery only runs if mail is configured separately.</p>
            <div class="actions-row">
                <a class="btn small secondary" href="<?= h(adminUrl('news/index.php')) ?>">Back to news</a>
                <a class="btn small" href="<?= h(adminUrl('news/subscribers.php?export=csv')) ?>">Export CSV</a>
            </div>
        </div>
    </div>
    <div class="panel">
        <form method="get" class="toolbar" style="margin:0;">
            <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search email…">
            <button class="btn small" type="submit">Search</button>
        </form>
    </div>
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr><th>Email</th><th>Status</th><th>Source</th><th>Subscribed</th><th></th></tr>
            </thead>
            <tbody>
            <?php if ($subscribers === []): ?>
                <tr><td colspan="5">No subscribers yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($subscribers as $row): ?>
                <tr>
                    <td><?= h((string) $row['email']) ?></td>
                    <td><?= h((string) $row['status']) ?></td>
                    <td><?= h((string) $row['source']) ?></td>
                    <td><?= h((string) $row['subscribed_at']) ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <?php if ((string) $row['status'] === 'active'): ?>
                                <input type="hidden" name="status" value="unsubscribed">
                                <button class="btn small secondary" type="submit">Unsubscribe</button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="active">
                                <button class="btn small" type="submit">Reactivate</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
