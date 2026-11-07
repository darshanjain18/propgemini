<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('news/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$slugInput = trim((string) ($_POST['slug'] ?? ''));
$kicker = trim((string) ($_POST['kicker'] ?? ''));
$summary = trim((string) ($_POST['summary'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));
$highlight = trim((string) ($_POST['highlight'] ?? ''));
$layout = (string) ($_POST['layout'] ?? 'editorial');
$status = (string) ($_POST['status'] ?? 'Draft');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$author = trim((string) ($_POST['author'] ?? ''));
$imageAlt = trim((string) ($_POST['image_alt'] ?? ''));
$imageCaption = trim((string) ($_POST['image_caption'] ?? ''));
$videoUrl = trim((string) ($_POST['video_url'] ?? ''));
$seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
$seoDescription = trim((string) ($_POST['seo_description'] ?? ''));
$ctaText = trim((string) ($_POST['cta_text'] ?? ''));
$ctaUrl = trim((string) ($_POST['cta_url'] ?? ''));
$offerTerms = trim((string) ($_POST['offer_terms'] ?? ''));
$relatedPropertyId = (int) ($_POST['related_property_id'] ?? 0);
$isFeatured = isset($_POST['is_featured']) ? 1 : 0;
$isHighlight = isset($_POST['is_highlight']) ? 1 : 0;
$highlightSort = max(0, min(99, (int) ($_POST['highlight_sort'] ?? 0)));
$readingOverride = trim((string) ($_POST['reading_time'] ?? ''));
$publishedAtInput = trim((string) ($_POST['published_at'] ?? ''));
$expiresAtInput = trim((string) ($_POST['expires_at'] ?? ''));

if (!isset(newsLayouts()[$layout])) {
    $layout = 'editorial';
}
if (!in_array($status, ['Draft', 'Published'], true)) {
    $status = 'Draft';
}

$redirect = $id > 0 ? 'news/edit.php?id=' . $id : 'news/edit.php';

if ($title === '' || mb_strlen($title) > 180) {
    adminFlash('error', 'A title of up to 180 characters is required.');
    adminRedirect($redirect);
}

if (
    mb_strlen($kicker) > 40
    || mb_strlen($summary) > 500
    || mb_strlen($highlight) > 120
    || mb_strlen($body) > 50000
    || mb_strlen($author) > 120
    || mb_strlen($imageAlt) > 180
    || mb_strlen($imageCaption) > 255
    || mb_strlen($videoUrl) > 500
    || mb_strlen($seoTitle) > 180
    || mb_strlen($seoDescription) > 320
    || mb_strlen($ctaText) > 80
    || mb_strlen($ctaUrl) > 255
    || mb_strlen($offerTerms) > 500
) {
    adminFlash('error', 'One of the text fields is too long.');
    adminRedirect($redirect);
}

$pdo = getDatabaseConnection();
$existing = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM news_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $existing = $stmt->fetch() ?: null;
    if ($existing === null) {
        adminFlash('error', 'That news item was not found.');
        adminRedirect('news/index.php');
    }
}

if ($categoryId > 0) {
    $catCheck = $pdo->prepare('SELECT id FROM news_categories WHERE id = ? LIMIT 1');
    $catCheck->execute([$categoryId]);
    if ($catCheck->fetchColumn() === false) {
        $categoryId = 0;
    }
}

$imagePath = $existing['image_path'] ?? null;
if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $imagePath = storeUploadedFile($_FILES['image'], 'news', ['image/jpeg', 'image/png', 'image/webp'], 8_000_000);
    } catch (Throwable $e) {
        adminFlash('error', $e->getMessage());
        adminRedirect($redirect);
    }
}

$socialImage = $existing['social_image_path'] ?? null;
if (!empty($_FILES['social_image']) && ($_FILES['social_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $socialImage = storeUploadedFile($_FILES['social_image'], 'news', ['image/jpeg', 'image/png', 'image/webp'], 8_000_000);
    } catch (Throwable $e) {
        adminFlash('error', $e->getMessage());
        adminRedirect($redirect);
    }
}

$gallery = [];
$existingGallery = [];
if (!empty($existing['gallery_json'])) {
    $decoded = json_decode((string) $existing['gallery_json'], true);
    if (is_array($decoded)) {
        $existingGallery = array_values(array_filter(array_map('strval', $decoded)));
    }
}
$gallery = $existingGallery;
if (!empty($_FILES['gallery']['name']) && is_array($_FILES['gallery']['name'])) {
    $count = count($_FILES['gallery']['name']);
    for ($i = 0; $i < $count; $i++) {
        if (($_FILES['gallery']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $file = [
            'name' => $_FILES['gallery']['name'][$i],
            'type' => $_FILES['gallery']['type'][$i],
            'tmp_name' => $_FILES['gallery']['tmp_name'][$i],
            'error' => $_FILES['gallery']['error'][$i],
            'size' => $_FILES['gallery']['size'][$i],
        ];
        try {
            $gallery[] = storeUploadedFile($file, 'news', ['image/jpeg', 'image/png', 'image/webp'], 8_000_000);
        } catch (Throwable $e) {
            adminFlash('error', $e->getMessage());
            adminRedirect($redirect);
        }
    }
}
$gallery = array_slice($gallery, 0, 8);
$galleryJson = $gallery === [] ? null : json_encode($gallery, JSON_UNESCAPED_SLASHES);

$publishedAt = $existing['published_at'] ?? null;
if ($publishedAtInput !== '') {
    $ts = strtotime($publishedAtInput);
    $publishedAt = $ts === false ? null : date('Y-m-d H:i:s', $ts);
} elseif ($status === 'Published' && $publishedAt === null) {
    $publishedAt = date('Y-m-d H:i:s');
}
if ($status === 'Draft') {
    // keep published_at if previously set for schedule restore, else null
    if ($publishedAtInput === '') {
        $publishedAt = null;
    }
}

$expiresAt = null;
if ($expiresAtInput !== '') {
    $ts = strtotime($expiresAtInput);
    $expiresAt = $ts === false ? null : date('Y-m-d H:i:s', $ts);
}

$readingTime = $readingOverride !== ''
    ? max(1, min(60, (int) $readingOverride))
    : estimateNewsReadingTime($body, $summary);

$slugBase = $slugInput !== '' ? $slugInput : $title;
$slug = uniqueNewsSlug($pdo, $slugBase, $id > 0 ? $id : null);

if ($isFeatured === 1) {
    $pdo->exec('UPDATE news_posts SET is_featured = 0');
}

if ($isHighlight === 1) {
    $highlightCount = (int) $pdo->query('SELECT COUNT(*) FROM news_posts WHERE is_highlight = 1')->fetchColumn();
    $already = false;
    if ($id > 0 && $existing !== null) {
        $already = (int) ($existing['is_highlight'] ?? 0) === 1;
    }
    if (!$already && $highlightCount >= 3) {
        $pdo->exec(
            'UPDATE news_posts SET is_highlight = 0
             WHERE id = (
                SELECT id FROM (
                    SELECT id FROM news_posts
                    WHERE is_highlight = 1
                    ORDER BY highlight_sort DESC, id DESC
                    LIMIT 1
                ) t
             )'
        );
    }
}

$params = [
    $title,
    $slug,
    $kicker !== '' ? $kicker : null,
    $summary !== '' ? $summary : null,
    $body !== '' ? $body : null,
    $highlight !== '' ? $highlight : null,
    $imagePath,
    $layout,
    $status,
    $publishedAt,
    $categoryId > 0 ? $categoryId : null,
    $isFeatured,
    $isHighlight,
    $highlightSort,
    $readingTime,
    $author !== '' ? $author : null,
    $imageAlt !== '' ? $imageAlt : null,
    $imageCaption !== '' ? $imageCaption : null,
    $galleryJson,
    $videoUrl !== '' ? $videoUrl : null,
    $seoTitle !== '' ? $seoTitle : null,
    $seoDescription !== '' ? $seoDescription : null,
    $socialImage,
    $ctaText !== '' ? $ctaText : null,
    $ctaUrl !== '' ? $ctaUrl : null,
    $relatedPropertyId > 0 ? $relatedPropertyId : null,
    $offerTerms !== '' ? $offerTerms : null,
    $expiresAt,
];

if ($id > 0) {
    $params[] = $id;
    $pdo->prepare(
        'UPDATE news_posts SET
            title=?, slug=?, kicker=?, summary=?, body=?, highlight=?, image_path=?, layout=?, status=?, published_at=?,
            category_id=?, is_featured=?, is_highlight=?, highlight_sort=?, reading_time=?, author=?,
            image_alt=?, image_caption=?, gallery_json=?, video_url=?, seo_title=?, seo_description=?,
            social_image_path=?, cta_text=?, cta_url=?, related_property_id=?, offer_terms=?, expires_at=?
         WHERE id=?'
    )->execute($params);
    logAdminActivity('news_update', 'news', $id, $title);
    adminFlash('success', 'News saved.');
} else {
    $pdo->prepare(
        'INSERT INTO news_posts
         (title, slug, kicker, summary, body, highlight, image_path, layout, status, published_at,
          category_id, is_featured, is_highlight, highlight_sort, reading_time, author,
          image_alt, image_caption, gallery_json, video_url, seo_title, seo_description,
          social_image_path, cta_text, cta_url, related_property_id, offer_terms, expires_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute($params);
    $id = (int) $pdo->lastInsertId();
    logAdminActivity('news_create', 'news', $id, $title);
    adminFlash('success', 'News added.');
}

adminRedirect('news/edit.php?id=' . $id);
