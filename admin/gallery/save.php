<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('gallery/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$caption = trim((string) ($_POST['caption'] ?? ''));
$status = (string) ($_POST['status'] ?? 'Draft');
$sort = (int) ($_POST['sort_order'] ?? 0);

if ($title === '' || mb_strlen($title) > 180 || mb_strlen($caption) > 400) {
    adminFlash('error', 'Add a title, and keep the caption short.');
    adminRedirect('gallery/index.php');
}
if (!in_array($status, ['Draft', 'Published'], true)) {
    $status = 'Draft';
}

$pdo = getDatabaseConnection();
$existing = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM gallery_items WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $existing = $stmt->fetch() ?: null;
    if ($existing === null) {
        adminFlash('error', 'That gallery item was not found.');
        adminRedirect('gallery/index.php');
    }
}

$filePath = $existing['file_path'] ?? '';
$mediaType = $existing['media_type'] ?? '';
$hasUpload = !empty($_FILES['media']) && ($_FILES['media']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($filePath === '' && !$hasUpload) {
    adminFlash('error', 'Choose an image or a video.');
    adminRedirect('gallery/index.php');
}

if ($hasUpload) {
    try {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $_FILES['media']['tmp_name']) ?: '';
        $images = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $videos = ['video/mp4', 'video/webm'];
        if (in_array($mime, $images, true)) {
            $mediaType = 'image';
            $filePath = storeUploadedFile($_FILES['media'], 'gallery', $images, 12_000_000);
        } elseif (in_array($mime, $videos, true)) {
            $mediaType = 'video';
            $filePath = storeUploadedFile($_FILES['media'], 'gallery', $videos, 80_000_000);
        } else {
            throw new RuntimeException('Gallery accepts images and videos only.');
        }
    } catch (Throwable $e) {
        adminFlash('error', $e->getMessage());
        adminRedirect('gallery/index.php');
    }
}

if ($id > 0) {
    $pdo->prepare(
        'UPDATE gallery_items SET title = ?, caption = ?, media_type = ?, file_path = ?, status = ?, sort_order = ? WHERE id = ?'
    )->execute([$title, $caption, $mediaType, $filePath, $status, $sort, $id]);
    logAdminActivity('gallery_update', 'gallery', $id, $title);
    adminFlash('success', 'Gallery item saved.');
} else {
    $pdo->prepare(
        'INSERT INTO gallery_items (title, caption, media_type, file_path, status, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$title, $caption, $mediaType, $filePath, $status, $sort]);
    logAdminActivity('gallery_create', 'gallery', (int) $pdo->lastInsertId(), $title);
    adminFlash('success', 'Added to the gallery.');
}

adminRedirect('gallery/index.php');
