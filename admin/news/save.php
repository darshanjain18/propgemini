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
$kicker = trim((string) ($_POST['kicker'] ?? ''));
$summary = trim((string) ($_POST['summary'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));
$highlight = trim((string) ($_POST['highlight'] ?? ''));
$layout = (string) ($_POST['layout'] ?? 'editorial');
$status = (string) ($_POST['status'] ?? 'Draft');

if (!isset(newsLayouts()[$layout])) {
    $layout = 'editorial';
}
if (!in_array($status, ['Draft', 'Published'], true)) {
    $status = 'Draft';
}

if ($title === '' || mb_strlen($title) > 180) {
    adminFlash('error', 'A title of up to 180 characters is required.');
    adminRedirect($id > 0 ? 'news/edit.php?id=' . $id : 'news/edit.php');
}
if (mb_strlen($kicker) > 40 || mb_strlen($summary) > 500 || mb_strlen($highlight) > 120 || mb_strlen($body) > 8000) {
    adminFlash('error', 'One of the text fields is too long.');
    adminRedirect($id > 0 ? 'news/edit.php?id=' . $id : 'news/edit.php');
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

$imagePath = $existing['image_path'] ?? null;
if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $imagePath = storeUploadedFile($_FILES['image'], 'news', ['image/jpeg', 'image/png', 'image/webp'], 8_000_000);
    } catch (Throwable $e) {
        adminFlash('error', $e->getMessage());
        adminRedirect($id > 0 ? 'news/edit.php?id=' . $id : 'news/edit.php');
    }
}

$publishedAt = $existing['published_at'] ?? null;
if ($status === 'Published' && $publishedAt === null) {
    $publishedAt = date('Y-m-d H:i:s');
}
if ($status === 'Draft') {
    $publishedAt = null;
}

$slug = uniqueNewsSlug($pdo, $title, $id > 0 ? $id : null);

if ($id > 0) {
    $pdo->prepare(
        'UPDATE news_posts
         SET title = ?, slug = ?, kicker = ?, summary = ?, body = ?, highlight = ?, image_path = ?, layout = ?, status = ?, published_at = ?
         WHERE id = ?'
    )->execute([$title, $slug, $kicker, $summary, $body, $highlight, $imagePath, $layout, $status, $publishedAt, $id]);
    logAdminActivity('news_update', 'news', $id, $title);
    adminFlash('success', 'News saved. It uses the ' . newsLayoutLabel($layout) . ' layout on the site.');
} else {
    $pdo->prepare(
        'INSERT INTO news_posts (title, slug, kicker, summary, body, highlight, image_path, layout, status, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$title, $slug, $kicker, $summary, $body, $highlight, $imagePath, $layout, $status, $publishedAt]);
    $id = (int) $pdo->lastInsertId();
    logAdminActivity('news_create', 'news', $id, $title);
    adminFlash('success', 'News added in the ' . newsLayoutLabel($layout) . ' layout.');
}

adminRedirect('news/index.php');
