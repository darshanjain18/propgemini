<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$id = (int) ($_GET['id'] ?? 0);
$post = [
    'title' => '',
    'kicker' => '',
    'summary' => '',
    'body' => '',
    'highlight' => '',
    'image_path' => '',
    'layout' => 'editorial',
    'status' => 'Draft',
];
$requestedLayout = (string) ($_GET['layout'] ?? '');
if ($id === 0 && isset(newsLayouts()[$requestedLayout])) {
    $post['layout'] = $requestedLayout;
}

if ($id > 0) {
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found === false) {
        adminFlash('error', 'That news item was not found.');
        adminRedirect('news/index.php');
    }
    $post = $found;
}

adminLayoutStart($id > 0 ? 'Edit news' : 'Add news', 'news');
?>
<div class="panel">
    <form method="post" action="<?= h(adminUrl('news/save.php')) ?>" enctype="multipart/form-data" class="stack">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <div class="field">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" maxlength="180" required value="<?= h((string) $post['title']) ?>">
        </div>
        <div class="field">
            <label for="kicker">Label</label>
            <input id="kicker" name="kicker" type="text" maxlength="40" value="<?= h((string) ($post['kicker'] ?? '')) ?>" placeholder="New launch, Offer, Price update">
        </div>
        <div class="field">
            <label for="layout">Layout</label>
            <select id="layout" name="layout">
                <?php foreach (newsLayouts() as $value => $label): ?>
                    <option value="<?= h($value) ?>"<?= (string) $post['layout'] === $value ? ' selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="help">Open the sample first. Every layout is already built, so you can compare them and then come back to save.</p>
            <p><a class="btn secondary" id="previewLayout" href="<?= h(adminUrl('news/preview.php')) ?>" target="_blank" rel="noopener">Preview layout</a></p>
        </div>
        <div class="field">
            <label for="highlight">Offer or price line</label>
            <input id="highlight" name="highlight" type="text" maxlength="120" value="<?= h((string) ($post['highlight'] ?? '')) ?>" placeholder="Shown as the callout, especially on the Offer layout">
        </div>
        <div class="field">
            <label for="summary">Summary</label>
            <textarea id="summary" name="summary" maxlength="500"><?= h((string) ($post['summary'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="body">Full note</label>
            <textarea id="body" name="body"><?= h((string) ($post['body'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="image">Image</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($post['image_path'])): ?>
                <p class="help">Current image stays unless you choose a new one.</p>
            <?php endif; ?>
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Draft"<?= (string) $post['status'] === 'Draft' ? ' selected' : '' ?>>Draft</option>
                <option value="Published"<?= (string) $post['status'] === 'Published' ? ' selected' : '' ?>>Published</option>
            </select>
        </div>
        <div class="actions-row">
            <button class="btn" type="submit">Save news</button>
            <a class="btn secondary" href="<?= h(adminUrl('news/index.php')) ?>">Cancel</a>
        </div>
    </form>
</div>
<script>
const layoutSelect = document.getElementById('layout');
const previewLink = document.getElementById('previewLayout');
if (layoutSelect && previewLink) {
    const previewBase = previewLink.href.split('?')[0];
    const idInput = document.querySelector('input[name="id"]');
    const syncPreview = () => {
        const id = idInput ? idInput.value : '0';
        const idQuery = id && id !== '0' ? '&id=' + encodeURIComponent(id) : '';
        previewLink.href = previewBase + '?layout=' + encodeURIComponent(layoutSelect.value) + idQuery;
    };
    layoutSelect.addEventListener('change', syncPreview);
    syncPreview();
}
</script>
<?php adminLayoutEnd(); ?>
