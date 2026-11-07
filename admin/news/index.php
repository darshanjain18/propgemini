<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$pdo = getDatabaseConnection();
$posts = $pdo->query('SELECT * FROM news_posts ORDER BY id DESC')->fetchAll();

adminLayoutStart('News', 'news');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:0;">
            <p>Write an update, choose a layout, and the public News page uses that same layout.</p>
            <div class="news-page-actions">
                <a class="btn small" href="<?= h(adminUrl('news/edit.php')) ?>">Add news</a>
                <a class="btn small secondary" href="<?= h(adminUrl('news/preview.php')) ?>" target="_blank" rel="noopener">Preview layouts</a>
            </div>
        </div>
    </div>
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr><th>Title</th><th>Layout</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php if ($posts === []): ?>
                <tr><td colspan="4">No news yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= h((string) $post['title']) ?></td>
                    <td><?= h(newsLayoutLabel((string) $post['layout'])) ?></td>
                    <td><?= h((string) $post['status']) ?></td>
                    <td>
                        <a href="<?= h(adminUrl('news/edit.php?id=' . (int) $post['id'])) ?>">Edit</a>
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
