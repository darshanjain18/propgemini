<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$pdo = getDatabaseConnection();
$items = $pdo->query('SELECT * FROM gallery_items ORDER BY sort_order ASC, id DESC')->fetchAll();
$editId = (int) ($_GET['id'] ?? 0);
$editing = [
    'id' => 0,
    'title' => '',
    'caption' => '',
    'status' => 'Published',
    'sort_order' => 0,
    'file_path' => '',
    'media_type' => '',
];
if ($editId > 0) {
    foreach ($items as $item) {
        if ((int) $item['id'] === $editId) {
            $editing = $item;
            break;
        }
    }
}

adminLayoutStart('Gallery', 'gallery');
?>
<div class="stack">
    <div class="panel">
        <div class="toolbar" style="margin-bottom:12px;">
            <div>
                <h3><?= (int) $editing['id'] > 0 ? 'Edit gallery item' : 'Add an image or video' ?></h3>
                <p>Only photographs and films. Published items appear on the Gallery page.</p>
            </div>
            <a class="btn small secondary" href="<?= h(adminUrl('gallery/preview.php')) ?>" target="_blank" rel="noopener">Preview gallery</a>
        </div>
        <form method="post" action="<?= h(adminUrl('gallery/save.php')) ?>" enctype="multipart/form-data" class="stack">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <div class="field">
                <label for="title">Title</label>
                <input id="title" name="title" type="text" maxlength="180" required value="<?= h((string) $editing['title']) ?>">
            </div>
            <div class="field">
                <label for="caption">Caption</label>
                <input id="caption" name="caption" type="text" maxlength="400" value="<?= h((string) ($editing['caption'] ?? '')) ?>">
            </div>
            <div class="field">
                <label for="media"><?= (int) $editing['id'] > 0 ? 'Replace file' : 'Image or video' ?></label>
                <input id="media" name="media" type="file" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm"<?= (int) $editing['id'] > 0 ? '' : ' required' ?>>
            </div>
            <div class="field">
                <label for="sort_order">Order</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Published"<?= (string) $editing['status'] === 'Published' ? ' selected' : '' ?>>Published</option>
                    <option value="Draft"<?= (string) $editing['status'] === 'Draft' ? ' selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="actions-row">
                <button class="btn" type="submit">Save</button>
                <?php if ((int) $editing['id'] > 0): ?>
                    <a class="btn secondary" href="<?= h(adminUrl('gallery/index.php')) ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr><th>Title</th><th>Type</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php if ($items === []): ?>
                <tr><td colspan="4">No gallery items yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= h((string) $item['title']) ?></td>
                    <td><?= h((string) $item['media_type']) ?></td>
                    <td><?= h((string) $item['status']) ?></td>
                    <td>
                        <a href="<?= h(adminUrl('gallery/index.php?id=' . (int) $item['id'])) ?>">Edit</a>
                        <form method="post" action="<?= h(adminUrl('gallery/delete.php')) ?>" style="display:inline" onsubmit="return confirm('Remove this from the gallery?')">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
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
