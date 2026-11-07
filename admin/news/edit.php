<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/layout.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

$id = (int) ($_GET['id'] ?? 0);
$post = [
    'title' => '',
    'slug' => '',
    'kicker' => '',
    'summary' => '',
    'body' => '',
    'highlight' => '',
    'image_path' => '',
    'layout' => 'editorial',
    'status' => 'Draft',
    'category_id' => null,
    'is_featured' => 0,
    'is_highlight' => 0,
    'highlight_sort' => 0,
    'reading_time' => null,
    'author' => 'PropGemini Desk',
    'image_alt' => '',
    'image_caption' => '',
    'gallery_json' => '',
    'video_url' => '',
    'seo_title' => '',
    'seo_description' => '',
    'social_image_path' => '',
    'cta_text' => '',
    'cta_url' => '',
    'related_property_id' => null,
    'offer_terms' => '',
    'published_at' => '',
    'expires_at' => '',
];

$requestedLayout = (string) ($_GET['layout'] ?? '');
if ($id === 0 && isset(newsLayouts()[$requestedLayout])) {
    $post['layout'] = $requestedLayout;
}

if ($id > 0) {
    $found = newsPostById($id);
    if ($found === null) {
        adminFlash('error', 'That news item was not found.');
        adminRedirect('news/index.php');
    }
    $post = array_merge($post, $found);
}

$categories = allNewsCategories();
$publishedAtValue = '';
if (!empty($post['published_at'])) {
    $ts = strtotime((string) $post['published_at']);
    if ($ts !== false) {
        $publishedAtValue = date('Y-m-d\TH:i', $ts);
    }
}
$expiresAtValue = '';
if (!empty($post['expires_at'])) {
    $ts = strtotime((string) $post['expires_at']);
    if ($ts !== false) {
        $expiresAtValue = date('Y-m-d\TH:i', $ts);
    }
}

adminLayoutStart($id > 0 ? 'Edit news' : 'Add news', 'news');
?>
<div class="panel">
    <form method="post" action="<?= h(adminUrl('news/save.php')) ?>" enctype="multipart/form-data" class="stack">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <h3 style="margin:0;">Basic information</h3>
        <div class="field">
            <label for="title">Headline</label>
            <input id="title" name="title" type="text" maxlength="180" required value="<?= h((string) $post['title']) ?>">
        </div>
        <div class="field">
            <label for="slug">URL slug</label>
            <input id="slug" name="slug" type="text" maxlength="190" value="<?= h((string) ($post['slug'] ?? '')) ?>" placeholder="Auto from headline if blank">
        </div>
        <div class="field">
            <label for="summary">Short description</label>
            <textarea id="summary" name="summary" maxlength="500"><?= h((string) ($post['summary'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="body">Full article content</label>
            <textarea id="body" name="body" rows="12"><?= h((string) ($post['body'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="0">— Select —</option>
                <?php foreach ($categories as $cat): ?>
                    <?php if ((string) $cat['slug'] === 'all-updates') {
                        continue;
                    } ?>
                    <option value="<?= (int) $cat['id'] ?>"<?= (int) ($post['category_id'] ?? 0) === (int) $cat['id'] ? ' selected' : '' ?>>
                        <?= h((string) $cat['name']) ?><?= (int) $cat['is_active'] === 1 ? '' : ' (inactive)' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="kicker">Display label (optional override)</label>
            <input id="kicker" name="kicker" type="text" maxlength="40" value="<?= h((string) ($post['kicker'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="layout">Content layout</label>
            <select id="layout" name="layout">
                <?php foreach (newsLayouts() as $value => $label): ?>
                    <option value="<?= h($value) ?>"<?= (string) $post['layout'] === $value ? ' selected' : '' ?>><?= h($label) ?></option>
                <?php endforeach; ?>
            </select>
            <p><a class="btn secondary" id="previewLayout" href="<?= h(adminUrl('news/preview.php')) ?>" target="_blank" rel="noopener">Preview layout</a></p>
        </div>
        <div class="field">
            <label for="highlight">Offer / callout line</label>
            <input id="highlight" name="highlight" type="text" maxlength="120" value="<?= h((string) ($post['highlight'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="author">Author / source</label>
            <input id="author" name="author" type="text" maxlength="120" value="<?= h((string) ($post['author'] ?? '')) ?>">
        </div>

        <h3 style="margin:18px 0 0;">Images and media</h3>
        <div class="field">
            <label for="image">Cover image</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($post['image_path'])): ?>
                <p class="help">Current: <a href="<?= h(webPath((string) $post['image_path'])) ?>" target="_blank" rel="noopener"><?= h((string) $post['image_path']) ?></a></p>
            <?php endif; ?>
        </div>
        <div class="field">
            <label for="image_alt">Image alt text</label>
            <input id="image_alt" name="image_alt" type="text" maxlength="180" value="<?= h((string) ($post['image_alt'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="image_caption">Image caption</label>
            <input id="image_caption" name="image_caption" type="text" maxlength="255" value="<?= h((string) ($post['image_caption'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="gallery">Additional images</label>
            <input id="gallery" name="gallery[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            <p class="help">Uploads are appended (max 8 total). Existing gallery stays unless you add more.</p>
        </div>
        <div class="field">
            <label for="video_url">Video URL (optional)</label>
            <input id="video_url" name="video_url" type="url" maxlength="500" value="<?= h((string) ($post['video_url'] ?? '')) ?>">
        </div>

        <h3 style="margin:18px 0 0;">Publication</h3>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Draft"<?= (string) $post['status'] === 'Draft' ? ' selected' : '' ?>>Draft</option>
                <option value="Published"<?= (string) $post['status'] === 'Published' ? ' selected' : '' ?>>Published</option>
            </select>
            <p class="help">Set a future publication date while Published to schedule the story.</p>
        </div>
        <div class="field">
            <label for="published_at">Publication date &amp; time</label>
            <input id="published_at" name="published_at" type="datetime-local" value="<?= h($publishedAtValue) ?>">
        </div>
        <div class="field">
            <label for="expires_at">Expiry date (offers)</label>
            <input id="expires_at" name="expires_at" type="datetime-local" value="<?= h($expiresAtValue) ?>">
        </div>
        <div class="field">
            <label><input type="checkbox" name="is_featured" value="1"<?= !empty($post['is_featured']) ? ' checked' : '' ?>> Featured article (homepage featured slot — only one)</label>
        </div>
        <div class="field">
            <label><input type="checkbox" name="is_highlight" value="1"<?= !empty($post['is_highlight']) ? ' checked' : '' ?>> Latest Highlights</label>
        </div>
        <div class="field">
            <label for="highlight_sort">Highlight order (1–3 preferred)</label>
            <input id="highlight_sort" name="highlight_sort" type="number" min="0" max="99" value="<?= (int) ($post['highlight_sort'] ?? 0) ?>">
        </div>
        <div class="field">
            <label for="reading_time">Reading time (minutes)</label>
            <input id="reading_time" name="reading_time" type="number" min="1" max="60" value="<?= h((string) ($post['reading_time'] ?? '')) ?>" placeholder="Auto from content if blank">
        </div>
        <div class="field">
            <label for="related_property_id">Related property ID (optional)</label>
            <input id="related_property_id" name="related_property_id" type="number" min="0" value="<?= (int) ($post['related_property_id'] ?? 0) ?>">
        </div>
        <div class="field">
            <label for="cta_text">CTA text</label>
            <input id="cta_text" name="cta_text" type="text" maxlength="80" value="<?= h((string) ($post['cta_text'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="cta_url">CTA URL</label>
            <input id="cta_url" name="cta_url" type="text" maxlength="255" value="<?= h((string) ($post['cta_url'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="offer_terms">Offer terms (offer layout)</label>
            <input id="offer_terms" name="offer_terms" type="text" maxlength="500" value="<?= h((string) ($post['offer_terms'] ?? '')) ?>">
        </div>

        <h3 style="margin:18px 0 0;">SEO</h3>
        <div class="field">
            <label for="seo_title">SEO title</label>
            <input id="seo_title" name="seo_title" type="text" maxlength="180" value="<?= h((string) ($post['seo_title'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="seo_description">Meta description</label>
            <textarea id="seo_description" name="seo_description" maxlength="320"><?= h((string) ($post['seo_description'] ?? '')) ?></textarea>
        </div>
        <div class="field">
            <label for="social_image">Social sharing image</label>
            <input id="social_image" name="social_image" type="file" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($post['social_image_path'])): ?>
                <p class="help">Current social image is saved.</p>
            <?php endif; ?>
        </div>

        <div class="actions-row">
            <button class="btn" type="submit">Save news</button>
            <a class="btn secondary" href="<?= h(adminUrl('news/index.php')) ?>">Back to list</a>
            <?php if ($id > 0 && (string) $post['status'] === 'Published'): ?>
                <a class="btn secondary" href="<?= h(newsArticleUrl((string) $post['slug'])) ?>" target="_blank" rel="noopener">View on site</a>
            <?php endif; ?>
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
