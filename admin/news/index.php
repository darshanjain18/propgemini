<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$pdo = getDatabaseConnection();
$q = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$layoutFilter = (string) ($_GET['layout'] ?? '');

$sql = 'SELECT n.*, c.name AS category_name
        FROM news_posts n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE 1=1';
$params = [];
if ($q !== '') {
    $sql .= ' AND (n.title LIKE ? OR n.summary LIKE ? OR n.slug LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if (in_array($statusFilter, ['Draft', 'Published'], true)) {
    $sql .= ' AND n.status = ?';
    $params[] = $statusFilter;
}
if (isset(newsLayouts()[$layoutFilter])) {
    $sql .= ' AND n.layout = ?';
    $params[] = $layoutFilter;
}
$sql .= ' ORDER BY n.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM news_posts')->fetchColumn(),
    'published' => (int) $pdo->query("SELECT COUNT(*) FROM news_posts WHERE status = 'Published'")->fetchColumn(),
    'draft' => (int) $pdo->query("SELECT COUNT(*) FROM news_posts WHERE status = 'Draft'")->fetchColumn(),
    'scheduled' => (int) $pdo->query(
        "SELECT COUNT(*) FROM news_posts WHERE status = 'Published' AND published_at IS NOT NULL AND published_at > NOW()"
    )->fetchColumn(),
    'featured' => (int) $pdo->query('SELECT COUNT(*) FROM news_posts WHERE is_featured = 1')->fetchColumn(),
];

adminLayoutStart('News', 'news');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:12px;">
            <div>
                <h2 style="margin:0 0 6px;font-size:22px;">News dashboard</h2>
                <p style="margin:0;color:#6d6458;">Create, schedule and feature stories for the public News page.</p>
            </div>
            <div class="news-page-actions">
                <a class="btn small" href="<?= h(adminUrl('news/edit.php')) ?>">Add news</a>
                <a class="btn small secondary" href="<?= h(adminUrl('news/categories.php')) ?>">Categories</a>
                <a class="btn small secondary" href="<?= h(adminUrl('news/subscribers.php')) ?>">Subscribers</a>
                <a class="btn small secondary" href="<?= h(adminUrl('news/preview.php')) ?>" target="_blank" rel="noopener">Preview layouts</a>
            </div>
        </div>
        <div class="actions-row" style="gap:10px;flex-wrap:wrap;">
            <span class="help"><strong><?= (int) $stats['total'] ?></strong> total</span>
            <span class="help"><strong><?= (int) $stats['published'] ?></strong> published</span>
            <span class="help"><strong><?= (int) $stats['draft'] ?></strong> draft</span>
            <span class="help"><strong><?= (int) $stats['scheduled'] ?></strong> scheduled</span>
            <span class="help"><strong><?= (int) $stats['featured'] ?></strong> featured</span>
        </div>
    </div>

    <div class="panel">
        <form method="get" class="toolbar" style="margin:0;gap:10px;flex-wrap:wrap;">
            <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search headline, slug…" style="min-width:220px;">
            <select name="status">
                <option value="">All statuses</option>
                <option value="Published"<?= $statusFilter === 'Published' ? ' selected' : '' ?>>Published</option>
                <option value="Draft"<?= $statusFilter === 'Draft' ? ' selected' : '' ?>>Draft</option>
            </select>
            <select name="layout">
                <option value="">All layouts</option>
                <?php foreach (newsLayouts() as $value => $label): ?>
                    <option value="<?= h($value) ?>"<?= $layoutFilter === $value ? ' selected' : '' ?>><?= h(newsLayoutLabel($value)) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn small" type="submit">Filter</button>
            <a class="btn small secondary" href="<?= h(adminUrl('news/index.php')) ?>">Reset</a>
        </form>
    </div>

    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Headline</th>
                    <th>Category</th>
                    <th>Layout</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Flags</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ($posts === []): ?>
                <tr><td colspan="8">No news match those filters.</td></tr>
            <?php endif; ?>
            <?php foreach ($posts as $post): ?>
                <?php
                $thumb = trim((string) ($post['image_path'] ?? ''));
                $flags = [];
                if ((int) ($post['is_featured'] ?? 0) === 1) {
                    $flags[] = 'Featured';
                }
                if ((int) ($post['is_highlight'] ?? 0) === 1) {
                    $flags[] = 'Highlight';
                }
                $pub = (string) ($post['published_at'] ?? '');
                $isScheduled = (string) $post['status'] === 'Published' && $pub !== '' && strtotime($pub) > time();
                ?>
                <tr>
                    <td>
                        <?php if ($thumb !== ''): ?>
                            <img src="<?= h(webPath($thumb)) ?>" alt="" width="54" height="40" style="object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                            <span class="help">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= h((string) $post['title']) ?></strong>
                        <div class="help"><?= h((string) $post['slug']) ?></div>
                    </td>
                    <td><?= h((string) ($post['category_name'] ?? '—')) ?></td>
                    <td><?= h(newsLayoutLabel((string) $post['layout'])) ?></td>
                    <td><?= h(formatNewsDate($pub !== '' ? $pub : (string) ($post['created_at'] ?? ''))) ?></td>
                    <td>
                        <?= h((string) $post['status']) ?>
                        <?php if ($isScheduled): ?><div class="help">Scheduled</div><?php endif; ?>
                    </td>
                    <td><?= h($flags === [] ? '—' : implode(' · ', $flags)) ?></td>
                    <td>
                        <a href="<?= h(adminUrl('news/edit.php?id=' . (int) $post['id'])) ?>">Edit</a>
                        <?php if ((string) $post['status'] === 'Published' && !$isScheduled): ?>
                            · <a href="<?= h(newsArticleUrl((string) $post['slug'])) ?>" target="_blank" rel="noopener">View</a>
                        <?php endif; ?>
                        <form method="post" action="<?= h(adminUrl('news/delete.php')) ?>" style="display:inline" onsubmit="return confirm('Remove this news item?')">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                            <button class="btn small secondary" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminLayoutEnd(); ?>
