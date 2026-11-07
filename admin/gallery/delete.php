<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('gallery/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare('SELECT title, file_path FROM gallery_items WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if ($row === false) {
    adminFlash('error', 'That gallery item was not found.');
    adminRedirect('gallery/index.php');
}

$pdo->prepare('DELETE FROM gallery_items WHERE id = ?')->execute([$id]);
$path = (string) ($row['file_path'] ?? '');
if (str_starts_with($path, 'public/uploads/gallery/')) {
    $absolute = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($absolute)) {
        unlink($absolute);
    }
}
logAdminActivity('gallery_delete', 'gallery', $id, (string) $row['title']);
adminFlash('success', 'Removed from the gallery.');
adminRedirect('gallery/index.php');
