<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/content.php';

requireAdmin();
ensureContentTables();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    adminFlash('error', 'Invalid request.');
    adminRedirect('news/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare('SELECT title FROM news_posts WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$title = $stmt->fetchColumn();
if ($title === false) {
    adminFlash('error', 'That news item was not found.');
    adminRedirect('news/index.php');
}

$pdo->prepare('DELETE FROM news_posts WHERE id = ?')->execute([$id]);
logAdminActivity('news_delete', 'news', $id, (string) $title);
adminFlash('success', 'News removed.');
adminRedirect('news/index.php');
